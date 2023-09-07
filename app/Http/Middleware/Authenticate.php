<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class Authenticate
{
    public function handle($request, Closure $next)
    {
        // 사용자가 인증되어 있지 않거나 비활성 상태인 경우 로그아웃 후 로그인 페이지로 리디렉션
        if (!Auth::check()) {

            return Redirect::route('auth.login');
        }
        if (Auth::user()->is_active !== 'ACTIVE') {
            Auth::logout();
            return Redirect::route('auth.login');
        }
        // 다음 미들웨어 또는 라우트로 요청을 전달
        return $next($request);
    }
}