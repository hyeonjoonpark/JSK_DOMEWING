<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function index()
    {
        $threeMROProducts = DB::table('minewing_products AS mp')
            ->where('sellerID', 16)
            ->where('isActive', 'Y')
            ->groupBy('categoryID')
            ->select('categoryID')
            ->get();
        foreach ($threeMROProducts as $categoryID) {
            $isExist = DB::table('category_mapping')
                ->where('ownerclan', $categoryID->categoryID)
                ->exists();
            if (!$isExist) {
                DB::table('category_mapping')
                    ->insert([
                        'ownerclan' => $categoryID->categoryID
                    ]);
            }
        }
    }
}
