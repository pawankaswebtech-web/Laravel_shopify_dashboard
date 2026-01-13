<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Opcodes\LogViewer\Facades\LogViewer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        LogViewer::auth(function ($request) {
            // return in_array($request->query('gmail'), explode(',', env('LOG_VIEWER_EMAILS', '')));
            return $request->user();
        });
    }
}
