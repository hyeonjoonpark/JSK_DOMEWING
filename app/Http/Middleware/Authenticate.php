<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        // API 요청인 경우, 리디렉션하지 않음 (JSON 응답을 예상함)
        if ($request->expectsJson()) {
            return null;
        }

        // 그 외의 경우, 로그인 페이지로 리디렉션
        return route('login');
    }
}