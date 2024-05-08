<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function partnerOpenMarket(Request $request)
    {
        $controller = new Controller();
        $openMarkets = $controller->getActiveOpenMarkets();
        return view('partner/open_market', [
            'openMarkets' => $openMarkets
        ]);
    }
}
