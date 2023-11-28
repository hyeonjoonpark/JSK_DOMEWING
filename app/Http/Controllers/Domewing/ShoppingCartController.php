<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    public function showShoppingCart(Request $request){

        $remember_token = $this->getShoppingCart();

        return view('domewing.shopping_cart', ['remember_token'=>$remember_token]);
    }

    public function getShoppingCart(){
        $user = Auth::guard('member')->user();

        //$remember_token = $user->remember_token;

        return $user;
    }
}
