<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CoupangAccount;

class OpenMarketController extends Controller
{
    public function index()
    {
        $partner = Auth::guard('partner')->user();
        $coupangAccount = CoupangAccount::where([
            'partner_id' => $partner->id,
            'is_active' => 'ACTIVE'
        ])->first();
        return view('partner/account_setting_open_market', [
            'coupangAccount' => $coupangAccount
        ]);
    }
}
