<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;

class AppUninstalledJob extends \Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob
{
    public function handle(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        CancelCurrentPlan $cancelCurrentPlanAction
    ): bool {
        try {
            Log::info("Handling App Uninstalled for domain: {$this->domain}");

            $shop = User::where('name', $this->domain)->first();

            if ($shop) {
                // Force delete the shop to remove it from the database completely
                $shop->forceDelete();
                Log::info("Shop force deleted: {$this->domain}");
                return true;
            }

            Log::warning("Shop not found for deletion: {$this->domain}");
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to force delete shop {$this->domain}: " . $e->getMessage());
            return false;
        }
    }
}
