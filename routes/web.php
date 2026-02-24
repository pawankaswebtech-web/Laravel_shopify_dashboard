<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
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
    Route::get('/home', [OrderController::class, 'HomeRoute'])->name('home');

    

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/api/orders', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update');
  

});
    Route::get('/register', [RegisterController::class,'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class,'register']);

   Route::get('/', [LoginController::class, 'showLogin'])->name('login.form');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');
    

  Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Route::get('/stores', [DashboardController::class, 'stores'])
    //     ->name('stores');

    // Route::get('/logs', [DashboardController::class, 'logs'])
    //     ->name('logs');

    Route::get('/swagger', [DashboardController::class, 'swagger'])->name('swagger');
    Route::get('/orders/{id}/download-json', [OrderController::class, 'downloadJson'])->name('orders.download.json');
    Route::post('/orders/{id}/resend-data', [OrderController::class, 'resendOrderData'])->name('orders.resend-data');
    Route::get('/order-details', [OrderController::class, 'orderDetail'])->name('orders.orderdetails');
    Route::get('/order-details-view/{userId}', [OrderController::class, 'orderDetailView'])->name('orders.detailview');
    Route::get('/order-manage-status/{shopOrderId}/status', [OrderController::class, 'showOrderStatusForm'])->name('orders.status');
    Route::post('/order-manage-status/{shopOrderId}/status', [OrderController::class, 'OrderStatus'])->name('orders.status.update');

    // Webhook for internal system updates (Unauthenticated)
    Route::post('/order/order-updates', [OrderController::class, 'webhookUpdateStatus'])->name('webhook.order.update');
});

 