<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function (\Illuminate\Http\Request $request) {
//     if (\Illuminate\Support\Facades\Auth::check()) {
//         return redirect()->route('home');
//     }
//     if ($request->has('shop')) {
//         return redirect()->route('home', $request->all());
//     }
//     return view('landing');
// })->name('landing');
Route::middleware(['verify.shopify'])->group(function () {
 
    Route::get('/', [OrderController::class, 'new'])->name('home');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/api/orders', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update');
    Route::get('/orders/{id}/download-json', [OrderController::class, 'downloadJson'])->name('orders.download.json');

});


Route::get('/order-details', [OrderController::class, 'orderDetail'])->name('orders.orderdetails');
Route::get('/order-details-view/{userId}', [OrderController::class, 'orderDetailView'])->name('orders.detailview');
Route::get('/order-manage-status/{shopOrderId}/status', [OrderController::class, 'showOrderStatusForm'])->name('orders.status');
Route::post('/order-manage-status/{shopOrderId}/status', [OrderController::class, 'OrderStatus'])->name('orders.status.update');

// Webhook for internal system updates (Unauthenticated)
Route::post('/order/order-updates', [OrderController::class, 'webhookUpdateStatus'])->name('webhook.order.update');