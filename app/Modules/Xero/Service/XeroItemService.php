<?php

namespace App\Modules\Xero\Service;

use App\Modules\Product\Domain\Product;
use XeroAPI\XeroPHP\ApiException;
use XeroAPI\XeroPHP\Models\Accounting\Item;
use XeroAPI\XeroPHP\Models\Accounting\Items;
use XeroAPI\XeroPHP\Models\Accounting\Purchase;

class XeroItemService
{
    public function __construct(private XeroAuthService $xeroAuthService) {}

    /**
     * Push a local Product to Xero as an Item.
     * Retries once with a forced token refresh if Xero returns 401.
     * Returns the Xero ItemID.
     */
    public function pushProduct(Product $product): string
    {
        try {
            return $this->doCreate($product);
        } catch (ApiException $e) {
            if ($e->getCode() === 401) {
                // Token was stale despite the proactive refresh — force a new one and retry
                $this->xeroAuthService->forceRefresh();
                return $this->doCreate($product);
            }
            throw $e;
        }
    }

    private function doCreate(Product $product): string
    {
        $salesDetails = (new Purchase())
            ->setUnitPrice((float) $product->retail_price);

        $purchaseDetails = (new Purchase())
            ->setUnitPrice((float) $product->landing_cost_price);

        $item = (new Item())
            ->setCode($product->code)
            ->setName("Name test ({$product->code})")
            ->setDescription("Description test ({$product->serial_tracking})")
            ->setPurchaseDescription("Purchase Description test ({$product->serial_tracking})")
            ->setSalesDetails($salesDetails)
            ->setPurchaseDetails($purchaseDetails);

        $items = new Items();
        $items->setItems([$item]);

        $api      = $this->xeroAuthService->getAccountingApi();
        $tenantId = $this->xeroAuthService->getTenantId();

        $result = $api->createItems($tenantId, $items, true);

        return $result->getItems()[0]->getItemId();
    }
}
