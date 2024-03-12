<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class CustomAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rememberToken = $request->rememberToken;

        if (empty($rememberToken) || !$this->validateRememberToken($rememberToken)) {
            // 변경: Response 객체를 사용하여 JSON 응답 반환
            return new JsonResponse([
                'status' => false,
                'return' => '로그인 세션이 만료되었습니다. 다시 로그인해주십시오.'
            ], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized 상태 코드 사용
        }

        return $next($request);
    }

    /**
     * Validate the provided remember token.
     *
     * @param  string|null  $rememberToken
     * @return bool
     */
    private function validateRememberToken(?string $rememberToken): bool
    {
        return DB::table('users')->where('remember_token', $rememberToken)->exists();
    }
}
