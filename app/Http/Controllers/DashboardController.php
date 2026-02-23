<?php
namespace App\Http\Controllers;

class DashboardController extends Controller
{
  
public function index()
{
    return view('dashboard');
}

public function stores()
{
    return view('stores');
}

public function logs()
{
    return view('logs');
}

public function swagger()
{
    return redirect('/api/documentation');
}
}