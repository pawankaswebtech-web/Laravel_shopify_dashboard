<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\WebhookController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook for internal system updates (Unauthenticated)
// Route::post('/webhook/order-update', [OrdersController::class, 'webhookUpdateStatus'])->name('api.webhook.order.update');



Route::post('/webhook', [WebhookController::class, 'handle']);

Route::prefix('ordersdetail') ->middleware(['auth:sanctum']) ->name('api.orders.') ->group(function () {
        Route::post('/index', [OrderController::class, 'index'])->name('index');
        Route::any('storeid/{storeid}', [OrderController::class, 'show'])->name('show');
        Route::any('/orderprefix/{orderId}', [OrderController::class, 'showOrderPrefix'])->name('showOrderPrefix');
        Route::any('/orderid/{Id}', [OrderController::class, 'showOrderId'])->name('showOrderId');
        Route::get('/orderstatus', [OrderController::class, 'getOrdersByStatus'])->name('getOrdersByStatus');
        Route::post('/orders/{id}/status', [OrderController::class, 'updateOrderStatus'])->name('updateOrderStatus');
});