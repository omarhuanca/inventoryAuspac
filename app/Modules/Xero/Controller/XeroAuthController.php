<?php

namespace App\Modules\Xero\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\Xero\Service\XeroAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class XeroAuthController extends Controller
{
    private XeroAuthService $xeroAuthService;

    public function __construct(XeroAuthService $xeroAuthService)
    {
        $this->xeroAuthService = $xeroAuthService;
    }

    /**
     * @OA\Get(
     *     path="/api/xero/auth",
     *     summary="Redirect to Xero authorization screen",
     *     tags={"Xero"},
     *     description="Redirects the user to Xero's OAuth2 login and consent screen.",
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Xero authorization URL"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error"
     *     )
     * )
     */
    public function authorize(Request $request)
    {
        $provider = $this->xeroAuthService->buildProvider();
        ['url' => $authUrl, 'state' => $state] = $this->xeroAuthService->getAuthorizationUrl($provider);

        // Store state in cache (sessions unavailable in API routes).
        Cache::put('xero_oauth2_state', $state, now()->addMinutes(10));

        return redirect()->away($authUrl);
    }

    /**
     * @OA\Get(
     *     path="/api/xero/callback",
     *     summary="Handle Xero OAuth callback",
     *     tags={"Xero"},
     *     description="Receives the authorization code and state from Xero after user consent and completes the OAuth connection.",
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Authorization code returned by Xero",
     *         required=true,
     *         @OA\Schema(type="string", example="4/0AX4XfWhExampleCode")
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         description="OAuth state used for CSRF protection",
     *         required=true,
     *         @OA\Schema(type="string", example="random_generated_state")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xero connected successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Xero connected successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="tenant_id", type="string", example="12345678-abcd-1234-abcd-123456789abc")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Authorization code missing"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid OAuth state"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error"
     *     )
     * )
     */
    public function callback(Request $request)
    {
        // Xero returns ?error=... when the user denies access or there's a config issue
        if ($request->query('error')) {
            $error = $request->query('error');
            $desc  = $request->query('error_description', 'No description provided.');
            return ApiResponse::error("Xero authorization denied: [{$error}] {$desc}", 400);
        }

        $code  = $request->query('code');
        $state = $request->query('state');

        if (!$code) {
            return ApiResponse::error('No authorization code returned by Xero.', 400);
        }

        if (empty($state) || $state !== Cache::get('xero_oauth2_state')) {
            Cache::forget('xero_oauth2_state');
            return ApiResponse::error('Invalid OAuth state. Possible CSRF attack.', 422);
        }

        Cache::forget('xero_oauth2_state');

        try {
            $tenantId = $this->xeroAuthService->handleCallback($code);

            return ApiResponse::success('Xero connected successfully.', 200, [
                'tenant_id' => $tenantId,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Xero callback failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/xero/status",
     *     summary="Check Xero connection status",
     *     tags={"Xero"},
     *     description="Returns the current status of the Xero integration, including tenant connection details.",
     *     @OA\Response(
     *         response=200,
     *         description="Xero connection status retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Xero connection status."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="connected", type="boolean", example=true),
     *                 @OA\Property(property="tenant_id", type="string", example="12345678-abcd-1234-abcd-123456789abc"),
     *                 @OA\Property(property="tenant_name", type="string", example="Demo Company")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error"
     *     )
     * )
     */
    public function status()
    {
        try {
            $status = $this->xeroAuthService->getConnectionStatus();

            return ApiResponse::success('Xero connection status.', 200, $status);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}