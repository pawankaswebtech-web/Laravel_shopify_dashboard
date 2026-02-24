<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebhookLog;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        WebhookLog::create([
            'topic' => $request->header('apps'),
            'shop_domain' => $request->header('https://dev.shopify.com/dashboard/198611959/apps/308400193537/logs/webhooks'),
            'payload' => $request->all(),
            'received_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}