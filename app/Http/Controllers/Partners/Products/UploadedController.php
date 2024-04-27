<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadedController extends Controller
{
    public function index(Request $request)
    {
        $openMarkets = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('type', 'OPEN_MARKET')
            ->get();
        $selectedOpenMarketId = $request->input('openMarket', '');
        $selectedOpenMarket = DB::table('vendors')
            ->where('id', $selectedOpenMarketId)
            ->first();
        $uploadedProducts = [];
        if ($selectedOpenMarket !== null) {
            $vendorEngName = $selectedOpenMarket->name_eng;
            $margin = DB::table('sellwing_config')
                ->where('id', 1)
                ->first(['value'])
                ->value;
            $marginRate = $margin / 100 + 1;
            $uploadedProducts = DB::table($vendorEngName . '_uploaded_products AS up')
                ->join('minewing_products AS mp', 'mp.id', '=', 'up.product_id')
                ->join($vendorEngName . '_accounts AS va', 'va.id', '=', 'up.' . $vendorEngName . '_account_id')
                ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
                ->select([
                    'mp.productCode',
                    'mp.productName',
                    'mp.productImage',
                    'up.price',
                    DB::raw("CEIL((mp.productPrice * $marginRate)) AS productPrice"), // 계산식 수정
                    'mp.shipping_fee AS mp_shipping_fee',
                    'oc.name',
                    'up.shipping_fee AS up_shipping_fee'
                ])
                ->get();
        }
        return view('partner.products_uploaded', [
            'openMarkets' => $openMarkets,
            'uploadedProducts' => $uploadedProducts,
            'selectedOpenMarketId' => $selectedOpenMarketId
        ]);
    }
}
