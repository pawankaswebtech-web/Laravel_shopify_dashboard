<?php
namespace App\Http\Controllers;
use App\Models\WebhookLog;

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
    public function showLogs()
    {
       
        $logs = WebhookLog::orderBy('created_at', 'desc')->paginate(20);

        return view('logs.index', compact('logs'));
    }
}