<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterDuplicatesController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->products;
        $duplicates = $this->checkHref($products);
    }
    public function checkHref($productHrefs)
    {
        $duplicates = DB::table('collected_products')
            ->where('isActive', 'Y')
            ->whereIn('productHref', $productHrefs)
            ->get();
        return !$duplicates->isEmpty();
    }
}
