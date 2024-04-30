<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\NameController;
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
            ->orderByDesc('pp.created_at')
            ->select('mp.productCode', 'mp.productImage', 'pp.product_name AS productName', DB::raw("mp.productPrice * {$marginValue} AS productPrice"), 'ps.shipping_fee', 'oc.name', 'pp.created_at')
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

            $products = DB::table('minewing_products')
                ->whereIn('productCode', $productCodes)
                ->whereNotIn('id', $existingProductIds)
                ->get(['id', 'productName']);

            foreach ($products as $product) {
                DB::table('partner_products')
                    ->insert([
                        'product_id' => $product->id,
                        'partner_table_id' => $partnerTableId,
                        'product_name' => $product->productName
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
                ->destroy();
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
    public function editProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productName' => 'required|min:2',
            'productCode' => 'required|string|exists:minewing_products,productCode',
            'type' => 'required|string|in:CONFIRMED,TEST'
        ], [
            'productName' => "상품명은 최소 2자 이상이어야 합니다.",
            'productCode' => "유효한 상품이 아닙니다.",
            'type' => '유효한 접근이 아닙니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ];
        }
        $productCode = $request->productCode;
        $productName = $request->productName;
        $type = $request->type;
        $nc = new NameController();
        $processedProductName = $nc->index($productName);
        if ($type === 'TEST') {
            return [
                'status' => true,
                'message' => '',
                'data' => [
                    'processedProductName' => $processedProductName
                ]
            ];
        }

        return $this->editPartnerProduct($productCode, $productName);
    }
    public function editPartnerProduct($productCode, $productName)
    {
        try {
            DB::table('partner_products AS pp')
                ->join('minewing_products AS mp', 'mp.id', '=', 'pp.product_id')
                ->where('mp.productCode', $productCode)
                ->update([
                    'pp.product_name' => $productName
                ]);
            return [
                'status' => true,
                'message' => '해당 상품을 성공적으로 수정했습니다.',
                'data' => ''
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품을 수정하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
