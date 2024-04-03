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
        return view('partner.accounts_management', [
            'coupangAccounts' => $coupangAccounts
        ]);
    }
}
