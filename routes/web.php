<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\WebhookController;
use App\Models\WebhookLog;

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
Route::get('/swaggeres', function () {
    return redirect('/api/documentation');
})->name('swaggeres');


Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/home', [OrderController::class, 'HomeRoute'])->name('home');

    

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/api/orders', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update');
  

});
    Route::get('/register', [RegisterController::class,'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class,'register']);

   Route::get('/login', [LoginController::class, 'showLogin'])->name('login.form');
    Route::post('/', [LoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return redirect()->route('login', [
        'token' => $token,
        'email' => request()->get('email')
    ]);
})->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');
    
 Route::get('/dashboard/logs', [DashboardController::class, 'showLogs'])->name('logs.index');
  Route::middleware('auth')->group(function () {


     
        Route::delete('/dashboard/webhooks/delete-all', function () {
            WebhookLog::truncate(); // deletes all records
            return redirect('/dashboard/webhooks')->with('success', 'All logs deleted successfully.');
        })->name('logs.deleteAll');
    Route::post('/webhook', [WebhookController::class, 'handle']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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

 
