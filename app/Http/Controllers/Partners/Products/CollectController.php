<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectController extends Controller
{
    public function index(Request $request)
    {
        $searchKeyword = $request->input('searchKeyword', '');
        $query = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'Y')
            ->select('mp.id AS productID', 'mp.productCode', 'mp.productImage', 'mp.productName', 'mp.productPrice', 'mp.productHref', 'v.name', 'mp.createdAt');

        if (!empty($searchKeyword)) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('mp.productName', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('mp.productPrice', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('mp.productCode', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('v.name', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('mp.createdAt', 'like', '%' . $searchKeyword . '%');
            });
        }

        $products = $query->orderBy('createdAt', 'DESC')->paginate(500);

        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->get();

        return view('partner/products_collect', [
            'products' => $products,
            'searchKeyword' => $searchKeyword,
            'productCodesStr' => '',
            'b2bs' => $b2bs
        ]);
    }
}
