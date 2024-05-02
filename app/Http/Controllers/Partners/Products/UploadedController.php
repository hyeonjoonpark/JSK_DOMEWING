<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $selectedOpenMarketId = $request->input('selectedOpenMarketId', 51);
        $selectedOpenMarket = DB::table('vendors')
            ->where('id', $selectedOpenMarketId)
            ->first();
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
            ->join('partners AS p', 'p.id', '=', 'va.partner_id')
            ->where('up.is_active', 'Y')
            ->where('va.partner_id', Auth::guard('partner')->id())
            ->orderByDesc('up.created_at')
            ->select([
                'mp.productCode',
                'up.product_name AS upName',
                'mp.productName AS mpName',
                'mp.productImage',
                'up.price',
                DB::raw("CEIL((mp.productPrice * $marginRate)) AS productPrice"), // 계산식 수정
                'mp.shipping_fee AS mp_shipping_fee',
                'oc.name',
                'up.shipping_fee AS up_shipping_fee',
                'up.origin_product_no',
                'va.username',
                'up.created_at',
                'up.origin_product_no',
                'mp.createdAt AS mca'
            ])
            ->paginate(500);
        return view('partner.products_uploaded', [
            'openMarkets' => $openMarkets,
            'uploadedProducts' => $uploadedProducts,
            'selectedOpenMarketId' => $selectedOpenMarketId
        ]);
    }
    public function delete(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'productCodes' => 'required|array|min:1',
        //     'originProductNo' => 'required|array|min:2'
        // ], [
        //     'productCodes' => '삭제할 상품을 최소 1개 이상 선택해주세요.',
        //     'originProductNo' => '삭제할게 없어요 ㅠ'
        // ]);
        // if ($validator->fails()) {
        //     return [
        //         'status' => false,
        //         'message' => $validator->errors()->first()
        //     ];
        // }
        // $productCodes = $request->productCodes;
        // $originProductsNo = $request->originProductNo;
        // return $this->destroy($productCodes, $originProductsNo);
        return [
            'status' => true,
            'message' => 'success',
            'data' => $request
        ];
    }
    public function destroy()
    {
    }
}
