<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    public function partnerOpenMarket(Request $request)
    {
        $partnerId = Auth::guard('partner')->id();
        // 연동된 도매윙 계정이 있는지 검사.
        $hasSync = DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        if ($hasSync === false) {
            return redirect('/partner/account-setting/dowewing-integration/');
        }
        $controller = new Controller();
        $openMarkets = $controller->getActiveOpenMarkets();
        return view('partner/open_market', [
            'openMarkets' => $openMarkets
        ]);
    }
}
