<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Godwings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('partner');
        $partnerClassId = $guard->user()->partner_class_id;
        if ($partnerClassId === 4) {
            return $next($request);
        }
        $guard->logout();
        return redirect()->route('partner.login')->withErrors(['invalidLogin' => '올바른 접근이 아닙니다.']);
    }
}
