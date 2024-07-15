<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokwingOrderService extends Controller
{
    public function main()
    {
        $vendors = DB::table('vendors')
            ->where('type', 'B2B')
            ->where('is_active', 'ACTIVE')
            ->get();
        return [
            'vendors' => $vendors
        ];
    }
}
