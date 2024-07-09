<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GodwingIndexService extends Controller
{
    public function main()
    {
        $vendors = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->whereIn('type', ['SELLER', 'B2B'])
            ->get();
        return view('admin.godwing_index', [
            'vendors' => $vendors
        ]);
    }
}
