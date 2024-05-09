<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
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
        $validator = Validator::make($request->all(), [
            'originProductsNo' => 'required|array|min:1',
            'vendorId' => 'required|integer|exists:vendors,id'
        ], [
            'originProductNo' => '유효한 상품이 아닙니다.',
            'vendorId' => '유효한 오픈 마켓을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ];
        }
        $vendorId = $request->vendorId;
        $vendorEngName = DB::table('vendors')
            ->where('id', $vendorId)
            ->value('name_eng');
        $originProductsNo = DB::table($vendorEngName . '_uploaded_products')
            ->where('is_active', 'Y')
            ->whereIn('origin_product_no', $request->originProductsNo)
            ->pluck('origin_product_no');
        $errors = [];
        $success = 0;
        foreach ($originProductsNo as $originProductNo) {
            $functionName = $vendorEngName . 'DeleteRequest';
            $result = $this->$functionName($originProductNo, $vendorEngName);
            if ($result['status'] === false) {
                $error = $result['error'];
                $errors = [
                    'originProductNo' => $originProductNo,
                    'error' => $error
                ];
            } else {
                $success++;
            }
        }
        $dupResult = $this->destroyUploadedProducts($originProductsNo, $vendorEngName);
        return [
            'status' => true,
            'message' => '총 ' . count($originProductsNo) . '개의 상품들 중 ' . $success . '개의 상품들을 성공적으로 삭제했습니다.',
            'data' => [
                'success' => $success,
                'errors' => $errors,
                'dupResult' => $dupResult
            ]
        ];
    }
    public function destroyUploadedProducts($originProductsNo, $vendorEngName)
    {
        try {
            DB::table($vendorEngName . '_uploaded_products')
                ->whereIn('origin_product_no', $originProductsNo)
                ->update(['is_active' => 'N']);
            return [
                'status' => true,
                'message' => '상품셋을 성공적으로 삭제했습니다.',
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품셋을 삭제처리 하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function smart_storeDeleteRequest($originProductNo, $vendorEngName)
    {
        $ssac = new SmartStoreApiController();
        $account = DB::table($vendorEngName . '_accounts AS a')
            ->join($vendorEngName . '_uploaded_products AS up', 'up.' . $vendorEngName . '_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.application_id', 'a.secret', 'a.username'])
            ->first();
        $contentType = 'application/json;charset=UTF-8';
        $method = "delete";
        $url = 'https://api.commerce.naver.com/external/v2/products/origin-products/' . $originProductNo;
        return $ssac->builder($account, $contentType, $method, $url);
    }

    public function destroy($originProductsNo)
    {
        try {
            DB::table('partner_products AS pp')
                ->join('minewing_products AS mp', 'mp.id', '=', 'pp.product_id')
                ->whereIn('mp.productCode', $originProductsNo)
                ->delete();
            return [
                'status' => true,
                'message' => '선택된 상품들이 성공적으로 삭제되었습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품을 삭제하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function coupangDeleteRequest($originProductNo, $vendorEngName)
    {
        $account = DB::table($vendorEngName . '_accounts AS a')
            ->join($vendorEngName . '_uploaded_products AS up', 'up.' . $vendorEngName . '_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.hash', 'a.secret_key', 'a.access_key'])
            ->first();
        $cpac = new ApiController();
        $contentType = 'application/json;charset=UTF-8';
        $method = "delete";
        $url = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products/' . $originProductNo;
        return $cpac->builder($account->access_key, $account->secret_key, $method, $contentType, $url);
    }
}
