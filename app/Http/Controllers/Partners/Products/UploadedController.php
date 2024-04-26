<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadedController extends Controller
{
    public function index(Request $request)
    {
        $mc = new ManageController();
        $partnerId = Auth::guard('partner')->id();
        $partnerTables = $mc->getPartnerTables($partnerId);
        return view('partner.products_uploaded', [
            'partnerTables' => $partnerTables
        ]);
    }
}
