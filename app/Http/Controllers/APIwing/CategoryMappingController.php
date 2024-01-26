<?php

namespace App\Http\Controllers\APIwing;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CategoryMappingController extends Controller
{
    public function index()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            DB::table('threemro_products AS tp')
                ->join('ownerclan_category AS oc', 'oc.name', '=', 'tp.categoryName')
                ->whereNull('tp.categoryID')
                ->update(
                    [
                        'tp.categoryID' => DB::raw('oc.id'),
                        'tp.updatedAt' => now(),
                    ]
                );
            return [
                'status' => true,
                'return' => 'success',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage(),
            ];
        }
    }
}
