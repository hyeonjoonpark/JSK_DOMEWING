<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    public function showShoppingCart(Request $request){
        return view('domewing.shopping_cart');
    }
}
