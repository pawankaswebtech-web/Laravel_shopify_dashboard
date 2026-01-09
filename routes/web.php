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

Route::get('/', function (\Illuminate\Http\Request $request) {
    if ($request->has('shop')) {
        return redirect()->route('home', $request->all());
    }
    return view('landing');
})->name('landing');
Route::middleware(['verify.shopify'])->group(function () {
    Route::get('/app', function () {
        $shop = \Illuminate\Support\Facades\Auth::user();
        $orders = \App\Models\Order::where('user_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('welcome', compact('orders'));
    })->name('home');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/api/orders', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update');
    Route::get('/orders/{id}/download-json', [OrderController::class, 'downloadJson'])->name('orders.download.json');
});