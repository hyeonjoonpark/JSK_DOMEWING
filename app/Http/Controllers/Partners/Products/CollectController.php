<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectController extends Controller
{
    public function index(Request $request)
    {
        $productCodesStr = $request->input('productCodesStr', '');
        $searchKeyword = $request->input('searchKeyword', '');
        $categoryId = $request->input('categoryId', '');
        $controller = new Controller();
        $marginValue = $controller->getMarginValue();

        $query = DB::table('minewing_products AS mp')
            ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
            ->where('mp.isActive', 'Y')
            ->select('mp.productCode', 'mp.productImage', 'mp.productName', DB::raw("mp.productPrice * {$marginValue} AS productPrice"), 'ps.shipping_fee', 'oc.name');

        if ($searchKeyword) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('mp.productName', 'like', "%$searchKeyword%")
                    ->orWhere('mp.productPrice', 'like', "%$searchKeyword%")
                    ->orWhere('mp.productCode', 'like', "%$searchKeyword%");
            });
        }

        if ($productCodesStr) {
            $productCodesArr = explode(',', $productCodesStr);
            $productCodesArr = array_map('trim', $productCodesArr);
            $query->whereIn('mp.productCode', $productCodesArr);
        }

        if ($categoryId && $categoryId != '-1') {
            $query->where('mp.categoryID', $categoryId);
        }

        $products = $query->orderBy('createdAt', 'DESC')->paginate(500);
        $categories = $this->getCategories();

        return view('partner/products_collect', compact('products', 'searchKeyword', 'productCodesStr', 'categories', 'categoryId'));
    }
    public function getCategories()
    {
        $categories = DB::table('minewing_products AS mp')
            ->join('ownerclan_category AS oc', 'mp.categoryID', '=', 'oc.id')
            ->select('oc.id', 'oc.name')
            ->distinct()
            ->get();
        return $categories;
    }
}
