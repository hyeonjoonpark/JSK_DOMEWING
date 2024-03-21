<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Admin\FormProductController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ExcelwingController as ProductExcelwingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExcelwingController extends Controller
{
    private $excelwingController;
    private $formProductController;
    public function __construct()
    {
        $this->excelwingController = new ProductExcelwingController;
        $this->formProductController = new FormProductController;
    }
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'b2bId' => 'required|integer',
            'productCodes' => 'required|string|min:5'
        ], [
            'b2bId' => '유효한 B2B 업체를 선택해주세요.',
            'productCodes' => '먼저 수정할 상품들을 엑셀 양식에 맞춰 업로드해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->errors()->first()
            ];
        }
        $b2bId = $request->b2bId;
        $productCodes = explode(',', $request->productCodes);
        return $this->excelwing($b2bId, $productCodes);
    }
    private function excelwing($b2bId, $productCodes)
    {
        $products = DB::table('minewing_products AS mp')
            ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
            ->whereIn('mp.productCode', $productCodes)
            ->where('mp.isActive', 'Y')
            ->get(['mp.*', 'ps.shipping_fee']);
        $products = $products->toArray();
        $productsChunks = array_chunk($products, 500);
        $response = $this->excelwingController->getMarginRate($b2bId);
        if (!$response['status']) {
            return $response;
        }
        $marginRate = $response['return'];
        $response = $this->excelwingController->getVendor($b2bId);
        if (!$response['status']) {
            return $response;
        }
        $b2B = $response['return'];
        $vendorEngName = $b2B->name_eng;
        $formProductController = $this->formProductController;
        $downloadURLs = [];
        $shippingFee = '';
        foreach ($productsChunks as $index => $products) {
            $response = $formProductController->$vendorEngName($products, $marginRate, $vendorEngName, $shippingFee, $index);
            if ($response['status'] == true) {
                $downloadURLs[] = $response['return'];
            } else {
                return [
                    'status' => false,
                    'return' => '"엑셀 파일에 데이터를 기록하던 중 오류가 발생했습니다."'
                ];
            }
        }
        return [
            'status' => true,
            'return' => $downloadURLs
        ];
    }
}
