<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManageController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = Auth::guard('partner')->id();
        $hasTable = DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        $partnerId = Auth::guard('partner')->id();
        $partnerTables = $this->getPartnerTables($partnerId);

        $searchKeyword = $request->input('searchKeyword', '');
        $partnerTableToken = $request->input('partnerTableToken', '');
        $productCodesStr = $request->input('productCodeStr', '');

        $products = $this->getProductList($searchKeyword, $partnerTableToken, $partnerId);

        return view('partner.products_manage', [
            'partnerTables' => $partnerTables,
            'products' => $products,
            'searchKeyword' => $searchKeyword,
            'partnerTableToken' => $partnerTableToken,
            'productCodesStr' => $productCodesStr,
            'hasTable' => $hasTable
        ]);
    }
    public function getPartnerTables($partnerId)
    {
        return DB::table('partner_tables')
            ->where('is_active', 'Y')
            ->where('partner_id', $partnerId)
            ->get();
    }
    private function getProductList($searchKeyword, $partnerTableToken, $partnerId)
    {
        $query = DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y');

        if ($partnerTableToken) {
            $query->where('token', $partnerTableToken);
        }

        $partnerTableId = $query->first('id');
        if ($partnerTableId === null) {
            return null;
        }
        $partnerTableId = $partnerTableId->id;
        $controller = new Controller();
        $marginValue = $controller->getMarginValue();
        return DB::table('partner_products AS pp')
            ->join('minewing_products AS mp', 'pp.product_id', '=', 'mp.id')
            ->join('ownerclan_category AS oc', 'mp.categoryID', '=', 'oc.id')
            ->join('product_search AS ps', 'mp.sellerID', '=', 'ps.vendor_id')
            ->where('pp.partner_table_id', $partnerTableId)
            ->where('mp.productName', 'like', "%{$searchKeyword}%")
            ->select('mp.productCode', 'mp.productImage', 'mp.productName', DB::raw("mp.productPrice * {$marginValue} AS productPrice"), 'ps.shipping_fee', 'oc.name')
            ->paginate(500);
    }
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productCodes' => 'required|array|min:1',
            'partnerTableToken' => 'required|string'
        ], [
            'productCodes' => '수집할 상품을 선택해주세요.',
            'partnerTableToken' => '올바른 접근이 아닙니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $productCodes = $request->productCodes;
        $partnerTableToken = $request->partnerTableToken;
        return $this->create($productCodes, $partnerTableToken);
    }
    private function create($productCodes, $partnerTableToken)
    {
        try {
            $partnerTableId = DB::table('partner_tables')
                ->where('token', $partnerTableToken)
                ->first()
                ->id;

            $existingProductIds = DB::table('partner_products')
                ->where('partner_table_id', $partnerTableId)
                ->pluck('product_id')
                ->toArray();

            $productIds = DB::table('minewing_products')
                ->whereIn('productCode', $productCodes)
                ->whereNotIn('id', $existingProductIds) // 이미 수집된 상품 ID 제외
                ->pluck('id')
                ->toArray();

            foreach ($productIds as $productId) {
                DB::table('partner_products')
                    ->insert([
                        'product_id' => $productId,
                        'partner_table_id' => $partnerTableId
                    ]);
            }
            return [
                'status' => true,
                'message' => '새로운 상품이 성공적으로 추가되었습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품을 수집하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * 다중 상품을 삭제하는 메소드.
     * @param Request $request
     * @return array
     */
    public function deleteProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productCodes' => 'required|array|min:1'
        ], [
            'productCodes' => '삭제할 상품을 최소 1개 이상 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $productCodes = $request->productCodes;
        return $this->destroy($productCodes);
    }
    public function destroy($productCodes)
    {
        try {
            DB::table('partner_products AS pp')
                ->join('minewing_products AS mp', 'mp.id', '=', 'pp.product_id')
                ->whereIn('mp.productCode', $productCodes)
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
}
