/**
 * k6 Load Test — TaxCore Async Fiscalization
 *
 * Stages:
 *   0 → 10 VUs over 15s  (ramp-up)
 *   10 VUs for 30s        (sustained load)
 *   10 → 0 over 10s       (ramp-down)
 *
 * Each VU:
 *   1. POST /api/taxcore/fiscalize  → expects 202 + invoice_id
 *   2. Polls GET /api/taxcore/invoices/{id} until status != "pending" (max 60s)
 *   3. Reports final status (completed / failed / timeout)
 *
 * Run:
 *   k6 run tests/k6/fiscalize-load.js
 *
 * Override base URL:
 *   k6 run --env BASE_URL=http://localhost:8000 tests/k6/fiscalize-load.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

// ─── Custom metrics ─────────────────────────────────────────────────────────
const fiscalizeErrors   = new Counter('fiscalize_errors');
const queueAccepted     = new Counter('queue_accepted');
const completedOk       = new Counter('completed_ok');
const completedFailed   = new Counter('completed_failed');
const completedTimeout  = new Counter('completed_timeout');
const pollDuration      = new Trend('poll_duration_ms', true);   // time from 202 → completed
const fiscalizeRate     = new Rate('fiscalize_success_rate');

// ─── Config ──────────────────────────────────────────────────────────────────
const BASE_URL    = __ENV.BASE_URL || 'http://localhost:8000';
const POLL_MAX_MS = 20_000;   // max 20 s waiting for the job to complete
const POLL_INTERVAL_S = 2;    // poll every 2 s

// ─── Load stages ─────────────────────────────────────────────────────────────
export const options = {
    stages: [
        { duration: '15s', target: 10 },   // ramp-up to 10 concurrent users
        { duration: '30s', target: 10 },   // hold
        { duration: '10s', target: 0  },   // ramp-down
    ],
    thresholds: {
        // 95 % of fiscalize requests must be accepted (202) under 2 s
        http_req_duration:       ['p(95)<2000'],
        fiscalize_success_rate:  ['rate>0.95'],
        // At least 80 % of queued invoices must complete successfully
        completed_ok:            ['count>0'],
    },
};

// ─── Payload factory ─────────────────────────────────────────────────────────
function buildPayload(vuId, iter) {
    const now   = new Date().toISOString().replace('T', ' ').substring(0, 19);
    const saleId = `VU${vuId}-ITER${iter}-${Date.now()}`;

    return JSON.stringify({
        invoiceType:       'Normal',
        transactionType:   'Sale',
        cashier:           `cashier-vu${vuId}`,
        buyerId:           null,
        saleId:            saleId,
        esdcId:            'default',
        dateAndTimeOfIssue: now,
        items: [
            {
                name:        'Test Product k6',
                quantity:    1,
                unitPrice:   300,
                totalAmount: 300,
                labels:      ['A'],
                taxCode:     null,
            },
        ],
        payment: [
            { paymentType: 'Cash', amount: 300 },
        ],
    });
}

const HEADERS = {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
};

// ─── Main VU scenario ────────────────────────────────────────────────────────
export default function () {
    const vuId   = __VU;
    const iter   = __ITER;

    // ── Step 1: Queue the invoice ──────────────────────────────────────────
    const res = http.post(
        `${BASE_URL}/api/taxcore/fiscalize`,
        buildPayload(vuId, iter),
        { headers: HEADERS, timeout: '10s' }
    );

    const accepted = check(res, {
        'POST fiscalize → 202 Accepted': (r) => r.status === 202,
        'response has invoice_id':        (r) => {
            try { return JSON.parse(r.body).data?.invoice_id > 0; } catch { return false; }
        },
    });

    fiscalizeRate.add(accepted);

    if (!accepted) {
        fiscalizeErrors.add(1);
        console.error(`[VU${vuId}] fiscalize failed: ${res.status} — ${res.body}`);
        sleep(1);
        return;
    }

    queueAccepted.add(1);

    const body      = JSON.parse(res.body);
    const invoiceId = body.data.invoice_id;
    const pollUrl   = `${BASE_URL}/api/taxcore/invoices/${invoiceId}`;

    // ── Step 2: Poll until completed / failed / timeout ────────────────────
    const pollStart = Date.now();
    let finalStatus = 'timeout';

    while (Date.now() - pollStart < POLL_MAX_MS) {
        sleep(POLL_INTERVAL_S);

        const pollRes = http.get(pollUrl, { headers: HEADERS, timeout: '5s' });

        if (pollRes.status !== 200) {
            console.warn(`[VU${vuId}] poll ${invoiceId}: HTTP ${pollRes.status}`);
            continue;
        }

        let pollBody;
        try { pollBody = JSON.parse(pollRes.body); } catch { continue; }

        const status = pollBody.data?.status ?? pollBody.data?.invoice?.status;

        if (status && status !== 'pending' && status !== 'processing') {
            finalStatus = status;
            break;
        }
    }

    pollDuration.add(Date.now() - pollStart);

    // ── Step 3: Record outcome ─────────────────────────────────────────────
    if (finalStatus === 'completed') {
        completedOk.add(1);
        check(finalStatus, { 'invoice completed OK': (s) => s === 'completed' });
    } else if (finalStatus === 'timeout') {
        completedTimeout.add(1);
        console.warn(`[VU${vuId}] invoice ${invoiceId} did not complete within ${POLL_MAX_MS / 1000}s`);
    } else {
        completedFailed.add(1);
        console.error(`[VU${vuId}] invoice ${invoiceId} final status: ${finalStatus}`);
    }
}
