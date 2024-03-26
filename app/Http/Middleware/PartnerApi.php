<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Partner;
use Illuminate\Support\Str;

class PartnerApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request)  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $apiToken = $request->apiToken;
        $validateApiToken = Partner::where('api_token', $apiToken)->exists();
        if ($validateApiToken === false) {
            return Response::json([
                'status' => false,
                'message' => '로그인 세션이 만료되었습니다. 다시 로그인해주십시오.'
            ], 401);
        }
        return $next($request);
    }
}
