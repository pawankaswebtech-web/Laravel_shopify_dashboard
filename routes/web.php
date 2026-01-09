<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/app', function () {
    return view('welcome');
})->middleware(['verify.shopify'])->name('home');
