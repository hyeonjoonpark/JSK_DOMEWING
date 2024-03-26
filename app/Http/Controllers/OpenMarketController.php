<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpenMarketController extends Controller
{
    public function index()
    {
        return view('partner/account_setting_open_market');
    }
}
