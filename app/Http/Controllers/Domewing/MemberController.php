<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function loadAccountSettings(Request $request){
        return view('domewing.user_details');
    }

    public function showToShip() {
        return view('domewing.to_ship');
    }

    public function showToReceive() {
        return view('domewing.to_receive');
    }

    public function showToRate() {
        return view('domewing.to_rate');
    }

    public function showPurchaseHistory() {
        return view('domewing.purchase_history');
    }

    public function showWishlist() {
        return view('domewing.wishlist');
    }
}
