<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (!$request->user() || !$request->user()->isAuthenticated()) {
            return $request->wantsJson() ? null : route('auth.login');
        }
    }
}