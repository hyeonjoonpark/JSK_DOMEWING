<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SoldOutController extends Controller
{
    public function index(Request $request)
    {
        $rememberToken = $request->rememberToken;
        $productCode = $request->productCode;
    }
}
