<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnersManagementController extends Controller
{
    public function index()
    {
        $partners = Partner::get();
        return view('admin/partners', [
            'partners' => $partners
        ]);
    }
}
