<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MembersAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 다음 미들웨어 또는 라우트로 요청을 전달
        $response = $next($request);

        // 캐시 제어 헤더를 추가하여 페이지가 캐시되지 않도록 설정
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');

        if (!Auth::guard('member')->check()) {
            // If user is not authenticated, logout and redirect to login
            Auth::guard('member')->logout();
            return redirect()->route('domewing.auth.login')->withErrors(['email' => 'You Must Login To Access'])->withInput();
        } else {
            $user = Auth::guard('member')->user();
            if ($user && $user->is_active === 'PENDING') {
                // Handle PENDING state, redirect to a verification page or show a message
                return redirect()->route('domewing.auth.login')->withErrors(['email' => 'Email Not Verified'])->withInput();
            }
        }

        return $response;
    }
}
