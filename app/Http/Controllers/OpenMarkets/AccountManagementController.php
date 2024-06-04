<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountManagementController extends Controller
{
    public function index(Request $request)
    {
        $apiToken = $request->input('apiToken', '');
        $partnerId = Auth::guard('partner')->id();
        $coupangAccounts = DB::table('coupang_accounts')
            ->where([
                'is_active' => 'ACTIVE',
                'partner_id' => $partnerId
            ])->get();
        $smartStoreAccounts = DB::table('smart_store_accounts')
            ->where([
                'is_active' => 'ACTIVE',
                'partner_id' => $partnerId
            ])->get();
        $st11Accounts = DB::table('st11_accounts')
            ->where([
                'is_active' => 'ACTIVE',
                'partner_id' => $partnerId
            ])->get();
        return view('partner.accounts_management', [
            'coupangAccounts' => $coupangAccounts,
            'smartStoreAccounts' => $smartStoreAccounts,
            'st11Accounts' => $st11Accounts
        ]);
    }
    public function domewing()
    {
        $partner = Auth::guard('partner')->user();
        $apiToken = $partner->api_token;
        $isExistPartnerAndDomewing = DB::table('partner_domewing_accounts')
            ->where('partner_id', $partner->id)
            ->where('is_active', 'Y')
            ->orderBy('created_at')
            ->exists();
        return view('partner/dowewing_integration', [
            'apiToken' => $apiToken,
            'isExistPartnerAndDomewing' => $isExistPartnerAndDomewing
        ]);
    }
}
