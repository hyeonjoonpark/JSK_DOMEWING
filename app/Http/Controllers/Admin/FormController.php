<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\Http\Controllers\Admin\CategoryMappingController;

class FormController extends Controller
{
    public function index(Request $request)
    {
        try {
            DB::statement("UPDATE collected_products AS cp
            SET cp.isActive = 'N'
            WHERE cp.isActive = 'Y' AND (
                cp.productName IN (
                    SELECT productName
                    FROM collected_products
                    WHERE isActive = 'Y'
                    GROUP BY productName
                    HAVING COUNT(*) > 1
                )
                OR cp.productHref IN (
                    SELECT productHref
                    FROM collected_products
                    WHERE isActive = 'Y'
                    GROUP BY productHref
                    HAVING COUNT(*) > 1
                )
            );");
            $collectedProducts = DB::select("SELECT cp.*
            FROM collected_products cp
            LEFT JOIN uploaded_products up ON up.productId = cp.id
            WHERE up.productId IS NULL
            AND cp.isActive = 'Y'
            LIMIT 500;");
            $pIC = new ProductImageController();
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            $processedProducts = [];
            foreach ($collectedProducts as $collectedProduct) {
                $collectedProduct->newImageHref = $pIC->index($collectedProduct->productImage);
                if ($collectedProduct->newImageHref == false) {
                    DB::table('collected_products')->where('id', $collectedProduct->id)->update([
                        'isActive' => 'N',
                        'remark' => 'Fail to load image'
                    ]);
                } else {
                    // $duplicated = DB::table('existed_product_name')->where('productName', $this->editProductName($collectedProduct->productName))->first();
                    // if ($duplicated) {
                    //     DB::table('collected_products')->where('id', $collectedProduct->id)->update([
                    //         'isActive' => 'N',
                    //         'remark' => 'Duplicated product'
                    //     ]);
                    // } else {
                    //     $collectedProduct->newProductName = $this->editProductName($collectedProduct->productName);
                    //     $processedProducts[] = $collectedProduct;
                    // }
                    $collectedProduct->newProductName = $this->editProductName($collectedProduct->productName);
                    $processedProducts[] = $collectedProduct;
                }
            }
            $userId = DB::table('users')->where('remember_token', $request->remember_token)->first()->id;
            $activedUploadVendors = DB::select("SELECT *
            FROM product_register pr
            INNER JOIN vendors v
            ON v.id=pr.vendor_id
            WHERE pr.is_active='Y';");
            $data['return']['successVendors'] = [];
            foreach ($activedUploadVendors as $vendor) {
                $vendorEngName = $vendor->name_eng;
                $response = $this->$vendorEngName($processedProducts, $userId);
                if ($response['status'] == 1) {
                    $data['return']['successVendors'][] = $vendor->name;
                }
            }
            $data['status'] = 1;
            return $data;
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage()
            ];
        }
    }
    public function domeggook($products, $userId)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 2;
            foreach ($products as $product) {
                $minAmount = 5000;
                $minQuantity = ceil($minAmount / $product->productPrice);
                $categoryId = $product->categoryId;
                $categoryMappingController = new CategoryMappingController();
                $categoryCode = $categoryMappingController->domeggookCategoryCode($categoryId);
                $data = [
                    '',
                    '도매꾹,도매매',
                    '직접판매',
                    'N',
                    $product->newProductName,
                    $product->keywords,
                    $categoryCode,
                    '상세정보별도표기',
                    '',
                    $product->productVendor,
                    'N',
                    'N',
                    '1',
                    '1',
                    '',
                    $product->newImageHref,
                    $product->productDetail,
                    '',
                    '',
                    '',
                    'Y',
                    '',
                    40,
                    '전체상세정보별도표시',
                    '전체상세정보별도표시',
                    'N',
                    $minQuantity . ':' . $product->productPrice,
                    '',
                    'N',
                    'N',
                    '1:' . $product->productPrice,
                    '',
                    '',
                    'N',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '99999',
                    '과세',
                    '',
                    '택배',
                    'Y',
                    '',
                    0,
                    '선결제:고정배송비',
                    $product->shippingCost,
                    '선결제:고정배송비',
                    $product->shippingCost,
                    '',
                    'SA0058243',
                    $product->shippingCost,
                    'N',
                    365,
                    'Y'
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $username = DB::table('users')
                ->join('accounts', 'accounts.user_id', '=', 'users.id')
                ->where('vendor_id', 5)
                ->value('accounts.username');
            $fileName = 'domeggook_' . $username . '_' . now()->format('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            // 응답 데이터 반환
            return [
                'status' => 1,
                'return' => $fileName,
            ];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function domero(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            // 카테고리 코드 변환을 위한 컨트롤러 생성
            $category = DB::table('category')->where('code', $request->category)->select('wholeCategoryName')->first()->wholeCategoryName;

            // 엑셀 파일 불러오기
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz.xlsx'));
            $sheet = $spreadsheet->getSheet(0);

            // 배송비 설정
            if ($request->shipping == '무료') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }

            $taxability = ($request->taxability === '과세') ? 'Y' : 'N';

            $shipping = '배송비' . $request->shipping;

            $saleToMinor = ($request->saleToMinor == '가능') ? 'Y' : 'N';

            // 제품 데이터 배열 생성
            $dataset = [
                'categoryCode' => $category,
                'productName' => $request->itemName,
                'isInternationalShipping' => '',
                'isSale' => '',
                'salesPermission' => '',
                'invoiceName' => '',
                'keywords' => $request->keywords,
                'taxability' => $taxability,
                'productPrice' => $request->price,
                'forcedPrice' => '',
                'bundledQuantity' => '가능수량',
                'shipType' => $shipping,
                'parcelType' => '',
                'shipCost' => $request->shipCost,
                'refundCost' => $shipCost,
                'jejuRefundCost' => $shipCost,
                'mountainRefundCost' => $shipCost,
                'vendor' => $request->vendor,
                'originated' => $request->origin,
                'brand' => $request->vendor,
                'productImage' => $productImage,
                'refundAddress' => '768',
                'saleToMinor' => $saleToMinor,
                'descImage' => $descImage,
                'option' => '',
                'optionDetail' => '',
                'credentialType' => '0',
                'credentialTitle' => '',
                'credentialCode' => '',
                'myCode' => '',
                'mycode2' => '',
                'productInformation' => '35',
                'productInformation0' => '상품 상세설명에 표시',
                'productInformation1' => '상품 상세설명에 표시',
                'productInformation2' => '상품 상세설명에 표시',
                'productInformation3' => '상품 상세설명에 표시',
                'productInformation4' => '상품 상세설명에 표시',
                'productInformation5' => '상품 상세설명에 표시',
                'productInformation6' => '상품 상세설명에 표시',
                'productInformation7' => '상품 상세설명에 표시',
                'productInformation8' => '상품 상세설명에 표시',
                'productInformation9' => '상품 상세설명에 표시',
                'productInformation10' => '상품 상세설명에 표시',
                'productInformation11' => '상품 상세설명에 표시',
                'productInformation12' => '상품 상세설명에 표시',
                'productInformation13' => '상품 상세설명에 표시',
                'productInformation14' => '상품 상세설명에 표시',
                'productInformation15' => '상품 상세설명에 표시',
                'productInformation16' => '상품 상세설명에 표시',
                'productInformation17' => '상품 상세설명에 표시',
                'productInformation18' => '상품 상세설명에 표시',
                'productInformation19' => '상품 상세설명에 표시',
                'productInformation20' => '상품 상세설명에 표시',
                'productInformation21' => '상품 상세설명에 표시',
            ];

            // 제품 정보를 엑셀에 추가
            $newRow = 4;
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }

            // 엑셀 파일 업로드
            $writer = new Xlsx($spreadsheet);
            $fileName = 'domero_' . $username . '_' . date('YmdHis') . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer->save($formedExcelFile);

            // 결과 반환
            $data['status'] = 1;
            $data['return'] = $fileName;
            return $data;
        } catch (Exception $e) {
            // 오류가 발생한 경우 처리
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
        }
    }
    public function domeatoz(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            // 카테고리 코드 변환을 위한 컨트롤러 생성
            $categoryMappingController = new CategoryMappingController();
            $categoryCode = $categoryMappingController->domeatozCategoryCode($categoryCode);

            // 엑셀 파일 불러오기
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz.xlsx'));
            $sheet = $spreadsheet->getSheet(0);

            // 배송비 설정
            if ($request->shipping == '무료') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }

            $taxability = ($request->taxability === '과세') ? 'Y' : 'N';

            $shipping = '배송비' . $request->shipping;

            $saleToMinor = ($request->saleToMinor == '가능') ? 'Y' : 'N';

            // 제품 데이터 배열 생성
            $dataset = [
                'categoryCode' => $categoryCode,
                'isInternationalShipping' => '',
                'isSale' => '',
                'productName' => $request->itemName,
                'salesPermission' => '',
                'invoiceName' => '',
                'keywords' => $request->keywords,
                'taxability' => $taxability,
                'productPrice' => $request->price,
                'forcedPrice' => '',
                'bundledQuantity' => '가능수량',
                'shipType' => $shipping,
                'parcelType' => '',
                'shipCost' => $request->shipCost,
                'refundCost' => $shipCost,
                'jejuRefundCost' => $shipCost,
                'mountainRefundCost' => $shipCost,
                'vendor' => $request->vendor,
                'originated' => $request->origin,
                'brand' => $request->vendor,
                'productImage' => $productImage,
                'refundAddress' => '768',
                'saleToMinor' => $saleToMinor,
                'descImage' => $descImage,
                'option' => '',
                'optionDetail' => '',
                'credentialType' => '0',
                'credentialTitle' => '',
                'credentialCode' => '',
                'myCode' => '',
                'mycode2' => '',
                'productInformation' => '35',
                'productInformation0' => '상품 상세설명에 표시',
                'productInformation1' => '상품 상세설명에 표시',
                'productInformation2' => '상품 상세설명에 표시',
                'productInformation3' => '상품 상세설명에 표시',
                'productInformation4' => '상품 상세설명에 표시',
                'productInformation5' => '상품 상세설명에 표시',
                'productInformation6' => '상품 상세설명에 표시',
                'productInformation7' => '상품 상세설명에 표시',
                'productInformation8' => '상품 상세설명에 표시',
                'productInformation9' => '상품 상세설명에 표시',
                'productInformation10' => '상품 상세설명에 표시',
                'productInformation11' => '상품 상세설명에 표시',
                'productInformation12' => '상품 상세설명에 표시',
                'productInformation13' => '상품 상세설명에 표시',
                'productInformation14' => '상품 상세설명에 표시',
                'productInformation15' => '상품 상세설명에 표시',
                'productInformation16' => '상품 상세설명에 표시',
                'productInformation17' => '상품 상세설명에 표시',
                'productInformation18' => '상품 상세설명에 표시',
                'productInformation19' => '상품 상세설명에 표시',
                'productInformation20' => '상품 상세설명에 표시',
                'productInformation21' => '상품 상세설명에 표시',
            ];

            // 제품 정보를 엑셀에 추가
            $newRow = 3;
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }

            // 엑셀 파일 업로드
            $writer = new Xlsx($spreadsheet);
            $fileName = 'domeatoz_' . $username . '_' . date('YmdHis') . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer->save($formedExcelFile);

            // 결과 반환
            $data['status'] = 1;
            $data['return'] = $fileName;
            return $data;
        } catch (Exception $e) {
            // 오류가 발생한 경우 처리
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
        }
    }
    public function wholesaledepot($products, $userId)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 5;
            foreach ($products as $product) {
                $categoryId = $product->categoryId;
                $categoryMappingController = new CategoryMappingController();
                $categoryCode = $categoryMappingController->wsConvertCategoryCode($categoryId);
                $data = [
                    $product->newProductName,
                    $product->newProductName,
                    $categoryCode,
                    '',
                    '',
                    '기타',
                    $product->productVendor,
                    $product->productVendor,
                    1,
                    0,
                    '',
                    0,
                    $product->keywords,
                    $product->productPrice,
                    '',
                    0,
                    0,
                    $product->productDetail,
                    $product->newImageHref,
                    '',
                    '',
                    '',
                    '',
                    '',
                    0,
                    0,
                    '',
                    'C',
                    '',
                    1597,
                    1,
                    '',
                    2,
                    0,
                    1,
                    'N',
                    '',
                    35,
                    '',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시',
                    '상품 상세설명에 표시'
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $username = DB::table('users')
                ->join('accounts', 'accounts.user_id', '=', 'users.id')
                ->where('vendor_id', 5)
                ->value('accounts.username');
            $fileName = 'wholesaledepot_' . $username . '_' . now()->format('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            // 응답 데이터 반환
            return [
                'status' => 1,
                'return' => $fileName,
            ];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }

    public function domesin($products, $userId)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $categoryId = $product->categoryId;
                $categoryCode = DB::table('category')->where('id', $categoryId)->select('domesinCode')->first()->domesinCode;
                $data = [
                    '',
                    $product->newProductName,
                    $categoryCode,
                    $product->newProductName,
                    '',
                    '기타',
                    $product->productVendor,
                    '',
                    '',
                    0,
                    0,
                    '',
                    'N',
                    $product->shippingCost,
                    $product->shippingCost,
                    '1508',
                    $product->keywords,
                    $product->productPrice,
                    '',
                    '',
                    $product->productDetail,
                    $product->newImageHref,
                    '',
                    '',
                    '',
                    '',
                    '',
                    0,
                    '',
                    '',
                    0,
                    '',
                    '',
                    1,
                    '',
                    0,
                    1,
                    '',
                    '',
                    35,
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                    '상품 상세설명 참조',
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $username = DB::table('users')
                ->join('accounts', 'accounts.user_id', '=', 'users.id')
                ->where('vendor_id', 6)
                ->value('accounts.username');
            $fileName = 'domesin_' . $username . '_' . now()->format('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xls($spreadsheet);
            $writer->save($formedExcelFile);
            // 응답 데이터 반환
            return [
                'status' => 1,
                'return' => $fileName,
            ];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function ownerclan($products, $userId)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/ownerclan.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $categoryId = $product->categoryId;
                $ownerclanCategoryCode = DB::table('category')->where('id', $categoryId)->select('code')->first()->code;
                $data = [
                    '',
                    $ownerclanCategoryCode,
                    '',
                    '',
                    '',
                    $product->newProductName,
                    $product->newProductName,
                    $product->keywords,
                    '기타',
                    $product->productVendor,
                    '',
                    $product->productPrice,
                    '자율',
                    '',
                    '과세',
                    '',
                    '',
                    '',
                    'N',
                    $product->newImageHref,
                    '',
                    $product->productDetail,
                    '가능',
                    '선불',
                    $product->shippingCost,
                    $product->shippingCost,
                    '',
                    '',
                    '',
                    1,
                    0,
                    '',
                    35,
                    '상품 상세설명 참조
                    상품 상세설명 참조
                    상품 상세설명 참조
                    상품 상세설명 참조
                    상품 상세설명 참조
                    상품 상세설명 참조
                    상품 상세정보에 별도 표기
                    상품 상세정보에 별도 표기
                    상품 상세정보에 별도 표기
                    상품 상세정보에 별도 표기',
                    0,
                    '',
                    '',
                    '',
                    '',
                    ''
                ];
                // 엑셀에 데이터 추가
                $colIndex = 1;
                foreach ($data as $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
                    $colIndex++;
                }
                $rowIndex++;
            }
            // 엑셀 파일 저장
            $username = DB::table('users')
                ->join('accounts', 'accounts.user_id', '=', 'users.id')
                ->where('vendor_id', 5)
                ->value('accounts.username');
            $fileName = 'ownerclan_' . $username . '_' . now()->format('YmdHis') . '.xlsx';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer = new Xlsx($spreadsheet);
            $writer->save($formedExcelFile);
            // 응답 데이터 반환
            return [
                'status' => 1,
                'return' => $fileName,
            ];
        } catch (Exception $e) {
            return [
                'status' => -1,
                'return' => $e->getMessage(),
            ];
        }
    }

    function editProductName($productName)
    {
        $byteLimit = 50;
        $byteCount = 0;
        $editedName = '';
        $previousCharWasSpace = false;

        for ($i = 0; $i < mb_strlen($productName, 'UTF-8') && $byteCount < $byteLimit; $i++) {
            $char = mb_substr($productName, $i, 1, 'UTF-8');

            // 한글, 숫자, 영어, 공백만 허용
            if (!preg_match('/[가-힣0-9a-zA-Z ]/u', $char)) {
                continue;
            }

            // 연속된 공백 방지
            if ($char == ' ') {
                if ($previousCharWasSpace) {
                    continue;
                }
                $previousCharWasSpace = true;
            } else {
                $previousCharWasSpace = false;
            }

            // 한글인 경우 2바이트로 계산
            if (preg_match('/[\x{AC00}-\x{D7AF}]/u', $char)) {
                $byteCount += 2;
            } else {
                // 그 외 문자는 1바이트로 계산
                $byteCount += 1;
            }

            if ($byteCount <= $byteLimit) {
                $editedName .= $char;
            }
        }

        return $editedName;
    }
}