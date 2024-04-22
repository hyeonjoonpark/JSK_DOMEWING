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
        $partnerTables = DB::table('partner_tables')
            ->where('is_active', 'Y')
            ->where('partner_id', $partnerId)
            ->get();

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
        return DB::table('partner_products AS pp')
            ->join('minewing_products AS mp', 'pp.product_id', '=', 'mp.id')
            ->join('ownerclan_category AS oc', 'mp.categoryID', '=', 'oc.id')
            ->join('product_search AS ps', 'mp.sellerID', '=', 'ps.vendor_id')
            ->where('pp.partner_table_id', $partnerTableId)
            ->where('pp.is_active', 'Y')
            ->where('mp.productName', 'like', "%{$searchKeyword}%")
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
                ->where('is_active', 'Y')
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
}
