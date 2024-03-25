<?php

namespace App\Http\Middleware;

use App\Models\Partner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatePartner
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('partner');
        if (!$guard->check()) {
            return redirect()->route('partner.login');
        }
        $partnerId = $guard->user()->id;
        $partner = Partner::where([
            'id' => $partnerId,
            'is_active' => 'ACTIVE'
        ])->first();
        if ($partner === null) {
            $guard->logout();
            return redirect()->route('partner.login')->withErrors(['invalidLogin' => '이용이 정지된 계정입니다.']);
        }
        if ($partner->expired_at < now() && $partner->partner_class_id !== 1) {
            $partnerId = $partner->id;
            Partner::where('id', $partnerId)->update([
                'partner_class_id' => 1
            ]);
            $guard->logout();
            return redirect()->route('partner.login')->withErrors(['invalidLogin' => '셀윙 파트너스 클래스가 만료되었습니다. 다시 로그인해주십시오.']);
        }
        view()->share('partner', $partner);
        return $next($request);
    }
}
