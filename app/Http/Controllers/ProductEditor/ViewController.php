<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewController extends Controller
{
    public function index(Request $request)
    {
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where([
                'v.is_active' => 'ACTIVE',
                'pr.is_active' => 'Y',
                'v.type' => 'B2B'
            ])->get();
        return view('admin/product_editor', [
            'b2bs' => $b2bs
        ]);
    }
}
