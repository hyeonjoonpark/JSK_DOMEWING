<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->products;
        $duplicates = [];
        foreach ($products as $product) {
            $product = explode(',', $product);
            $productHref = $product[0];
            $productName = $product[2];
            $fD = $this->filterDuplicated($productHref, $productName);
            if ($fD) {
            }
        }
    }
    public function getSeller($sellerName)
    {
        return DB::table('vendors')
            ->where('name', $sellerName)
            ->first();
    }
    public function filterDuplicated($productHref, $productName)
    {
        $duplicates = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->where(function ($query) use ($productName, $productHref) {
                $query->where('productName', $productName)
                    ->orWhere('productHref', $productHref);
            })
            ->where('up.isActive', 'Y')
            ->where('cp.isActive', 'Y')
            ->get();
        return !$duplicates->isEmpty();
    }
}
