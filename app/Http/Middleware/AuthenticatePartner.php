<?php

namespace App\Http\Middleware;

use App\Http\Controllers\WingController;
use App\Models\Partner;
use App\Models\PartnerClass;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $partner = Partner::with('partnerClass')->find($partner->id);
        $wingBalance = 0;
        $memberId = DB::table('members AS m')
            ->join('partner_domewing_accounts AS pda', 'pda.domewing_account_id', '=', 'm.id')
            ->where('pda.is_active', 'Y')
            ->where('pda.partner_id', $partnerId)
            ->first(['m.id']);
        if ($memberId !== null) {
            $memberId = $memberId->id;
            $wc = new WingController();
            $wingBalance = $wc->getBalance($memberId);
        }
        $notifications = DB::table('notifications')
            ->where('partner_id', $partnerId)
            ->where('read_at', null)
            ->orderByDesc('created_at')
            ->get();
        view()->share('partner', $partner);
        view()->share('wingBalance', $wingBalance);
        view()->share('notifications', $notifications);
        return $next($request);
    }
}
