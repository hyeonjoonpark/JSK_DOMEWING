<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function index()
    {
        $categories = DB::table('minewing_products')
            ->groupBy('categoryID')
            ->select('categoryID')
            ->get();
        foreach ($categories as $category) {
            $categoryID = $category->categoryID;
            $isExists = DB::table('category_mapping')
                ->where('ownerclan', $categoryID)
                ->exists();
            if (!$isExists) {
                DB::table('category_mapping')->insert([
                    'ownerclan' => $categoryID
                ]);
            }
        }
        return $categories;
    }
}
