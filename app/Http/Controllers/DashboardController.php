<?php
namespace App\Http\Controllers;

class DashboardController extends Controller
{
  
public function index()
{
    return view('dashboard');
}

public function swagger()
{
    return view('/api/documentation');
}
}