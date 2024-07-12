<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NalmeokwingStoreService extends Controller
{
    public function main(Request $request)
    {
        set_time_limit(0);
        $validator = $this->validator($request);
        if (!$validator['status']) {
            return $validator;
        }
        return $this->extractProducts($request->input('vendorId'), $request->file('file'));
    }
    protected function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorId' => ['required', 'exists:vendors,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv']
        ], [
            'vendorId.required' => '벤더 ID는 필수 항목입니다.',
            'vendorId.integer' => '벤더 ID는 정수여야 합니다.',
            'vendorId.exists' => '존재하지 않는 벤더 ID입니다.',
            'file.required' => '파일은 필수 항목입니다.',
            'file.file' => '업로드한 파일이 유효하지 않습니다.',
            'file.mimes' => '파일은 xlsx, xls, csv 형식이어야 합니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        return [
            'status' => true
        ];
    }
    protected function extractProducts(int $vendorId, UploadedFile $file)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        $vendorEngName = DB::table('vendors')
            ->where('id', $vendorId)
            ->value('name_eng');
        return $this->$vendorEngName($sheetData);
    }
    protected function ownerclan(array $sheetData)
    {
        $products = array_slice($sheetData, 2);
        $errors = [];
        foreach ($products as $i => $product) {
            $product = $this->processOwnerclan($product);
            $productValidatorResult = $this->productValidator($product);
            if (!$productValidatorResult['status']) {
                $errors[] = [
                    'index' => $i + 1,
                    'message' => $productValidatorResult['error']
                ];
                continue;
            }
            $storeResult = $this->store($product);
            if (!$storeResult['status']) {
                $errors[] = [
                    'index' => $i + 1,
                    'message' => $storeResult['message'],
                    'error' => $storeResult['error']
                ];
            }
        }
        return [
            'status' => true,
            'message' => '날먹윙 상품 생성을 성공적으로 마쳤습니다.',
            'error' => $errors
        ];
    }
    protected function processOwnerclan(array $product)
    {
        $sellerID = 5;
        $userID = 15;
        $categoryCode = $product[3];
        $categoryID = DB::table('ownerclan_category')
            ->where('code', $categoryCode)
            ->value('id');
        $originProductCode = $product[2];
        $productCode = $this->getProductCode('minewing_products', 8);
        $productName = $product[7];
        $productKeywords = $this->processProductKeywords($product[35]);
        $productPrice = $product[8];
        $shippingFee = $product[11];
        $bundleQuantity = $product[14];
        $productImage = $product[28];
        $productOptions = [
            'option1' => [
                'name' => $product[17],
                'value' => $product[18]
            ],
            'option2' => [
                'name' => $product[19],
                'value' => $product[20]
            ]
        ];
        $productDetail = $this->processProductDetail($product[39], $productOptions);
        $productHref = 'https://ownerclan.com/V2/product/view.php?selfcode=' . $originProductCode;
        $hasOption = $productOptions['option1']['value'] ? 'Y' : 'N';
        return [
            'sellerID' => $sellerID,
            'userID' => $userID,
            'categoryID' => $categoryID,
            'origin_product_code' => $originProductCode,
            'productCode' => $productCode,
            'productName' => $productName,
            'productKeywords' => $productKeywords,
            'productPrice' => $productPrice,
            'shipping_fee' => $shippingFee,
            'bundle_quantity' => $bundleQuantity,
            'productImage' => $productImage,
            'productDetail' => $productDetail,
            'productHref' => $productHref,
            'hasOption' => $hasOption
        ];
    }
    protected function processProductKeywords(string $productKeywords)
    {
        $productKeywordArr = explode(',', $productKeywords);
        $productKeywords = array_slice($productKeywordArr, 0, 10);
        return implode(',', $productKeywords);
    }
    protected function processProductDetail(string $productDetail, array $productOptions)
    {
        $nalmeokwingHeader = asset('images/CDN/nalmeokwing_header.jpg');
        $productOption = $this->processProductOptions($productOptions);
        $html = '
            <center>
                ' . $productOption . '
                <img src="' . $nalmeokwingHeader . '" style="width: 100%;">
            </center>
        ';
        return $html . $productDetail;
    }
    protected function processProductOptions(array $productOptions)
    {
        $html = '';
        if ($productOptions['option1']['value']) {
            $html = '
                <h1 style="color:red !important; font-weight:bold !important; font-size:4rem !important;">
                    옵션: ' . $productOptions['option1']['name'] . ' - ' . $productOptions['option1']['value'] . '
            ';
            if ($productOptions['option2']['value']) {
                $html .= ' / ' . $productOptions['option2']['name'] . ' - ' . $productOptions['option2']['value'];
            }
            $html .= '</h1><br><br><br>';
        }
        return $html;
    }
    protected function productValidator(array $product)
    {
        $validator = Validator::make($product, [
            'sellerID' => ['required', 'integer', 'exists:vendors,id'],
            'userID' => ['required', 'integer', 'exists:users,id'],
            'categoryID' => ['required', 'integer', 'exists:ownerclan_category,id'],
            'origin_product_code' => ['required', 'string'],
            'productCode' => ['required', 'string', 'unique:minewing_products,productCode'],
            'productName' => ['required', 'string', 'unique:minewing_products,productName'],
            'productKeywords' => ['required', 'string'],
            'productPrice' => ['required', 'integer'],
            'shipping_fee' => ['required', 'integer'],
            'bundle_quantity' => ['required', 'integer'],
            'productImage' => ['required', 'string'],
            'productDetail' => ['required', 'string'],
            'productHref' => ['required', 'string'],
            'hasOption' => ['required', 'string', 'in:Y,N']
        ], [
            'sellerID.required' => '판매자 ID는 필수 항목입니다.',
            'sellerID.integer' => '판매자 ID는 정수여야 합니다.',
            'sellerID.exists' => '존재하지 않는 판매자 ID입니다.',
            'userID.required' => '사용자 ID는 필수 항목입니다.',
            'userID.integer' => '사용자 ID는 정수여야 합니다.',
            'userID.exists' => '존재하지 않는 사용자 ID입니다.',
            'categoryID.required' => '카테고리 ID는 필수 항목입니다.',
            'categoryID.integer' => '카테고리 ID는 정수여야 합니다.',
            'categoryID.exists' => '존재하지 않는 카테고리 ID입니다.',
            'origin_product_code.required' => '원 제품 코드는 필수 항목입니다.',
            'origin_product_code.string' => '원 제품 코드는 문자열이어야 합니다.',
            'productCode.required' => '제품 코드는 필수 항목입니다.',
            'productCode.string' => '제품 코드는 문자열이어야 합니다.',
            'productCode.unique' => '이미 존재하는 제품 코드입니다.',
            'productName.required' => '제품 이름은 필수 항목입니다.',
            'productName.string' => '제품 이름은 문자열이어야 합니다.',
            'productName.unique' => '이미 존재하는 제품 이름입니다.',
            'productKeywords.required' => '제품 키워드는 필수 항목입니다.',
            'productKeywords.string' => '제품 키워드는 문자열이어야 합니다.',
            'productPrice.required' => '제품 가격은 필수 항목입니다.',
            'productPrice.integer' => '제품 가격은 정수여야 합니다.',
            'shipping_fee.required' => '배송료는 필수 항목입니다.',
            'shipping_fee.integer' => '배송료는 정수여야 합니다.',
            'bundle_quantity.required' => '번들 수량은 필수 항목입니다.',
            'bundle_quantity.integer' => '번들 수량은 정수여야 합니다.',
            'productImage.required' => '제품 이미지는 필수 항목입니다.',
            'productImage.string' => '제품 이미지는 문자열이어야 합니다.',
            'productDetail.required' => '제품 상세 정보는 필수 항목입니다.',
            'productDetail.string' => '제품 상세 정보는 문자열이어야 합니다.',
            'productHref.required' => '제품 링크는 필수 항목입니다.',
            'productHref.string' => '제품 링크는 문자열이어야 합니다.',
            'hasOption.required' => '옵션 여부는 필수 항목입니다.',
            'hasOption.string' => '옵션 여부는 문자열이어야 합니다.',
            'hasOption.in' => '옵션 여부는 Y 또는 N이어야 합니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'error' => $validator->errors()->first()
            ];
        }
        return [
            'status' => true
        ];
    }
    protected function store(array $product)
    {
        try {
            DB::table('minewing_products')
                ->insert($product);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
