<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokProductCreateController extends Controller
{
    public function main()
    {
        $vendors = DB::table('vendors')
            ->where('type', 'B2B')
            ->where('is_active', 'ACTIVE')
            ->get(['id', 'name']);
        return view('admin/nalmeok_product_create', [
            'vendors' => $vendors
        ]);
    }
}
