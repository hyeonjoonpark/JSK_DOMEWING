<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GodwingApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = $request->input('apiToken');
        $partnerClassId = DB::table('partners')
            ->where('api_token', $apiToken)
            ->value('partner_class_id');
        if ($partnerClassId === 4) {
            return $next($request);
        }
        return response()->json([
            'status' => false,
            'message' => '접근 권한이 없습니다.'
        ]);
    }
}
