<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index()
    {
        return view('partner.orders_list');
    }
}
