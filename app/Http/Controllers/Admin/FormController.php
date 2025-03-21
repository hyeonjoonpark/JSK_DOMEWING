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
    public function preprocessProductDataset()
    {
        try {
            // 수집된 상품 테이블에서 중복된 상품들 제거.
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            DB::statement("UPDATE collected_products
            SET isActive = 'N'
            WHERE id NOT IN (
                SELECT MIN(id)
                FROM collected_products
                GROUP BY productHref
            )
            AND productHref IN (
                SELECT productHref
                FROM collected_products
                GROUP BY productHref
                HAVING COUNT(*) > 1
            );");
            $data['status'] = 1;
            $data['return'] = 'success';
            return $data;
        } catch (Exception $e) {
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
        }
    }
    public function index(Request $request)
    {
        try {
            $remember_token = $request->remember_token;
            $userId = DB::table('users')
                ->where('remember_token', $remember_token)
                ->first()
                ->id;
            $processedProducts = $this->preprocessedProducts(890);
            $activedUploadVendors = DB::table('product_register')
                ->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')
                ->where('product_register.is_active', 'Y')
                ->where('vendors.is_active', 'ACTIVE')
                ->get();
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            foreach ($activedUploadVendors as $vendor) {
                $vendorEngName = $vendor->name_eng;
                $response = $this->$vendorEngName($processedProducts, $userId);
                if ($response['status'] == 1) {
                    $data['return']['successVendors'][] = $vendor->name;
                    $data['return']['successVendorsNameEng'][] = $vendorEngName;
                    $data['return']['formedExcelFiles'][] = $response['return'];
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
    public function preprocessProducts($userId)
    {
        $ppd = $this->preprocessProductDataset();
        if ($ppd['status'] === -1) {
            return $ppd;
        }
        $targetProducts = DB::select("SELECT cp.*
            FROM collected_products cp
            LEFT JOIN uploaded_products up ON up.productId = cp.id
            WHERE up.productId IS NULL
            AND cp.isActive='Y'
            LIMIT 100;");
        $pIC = new ProductImageController();
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $startProductID = 1;
        $cnt = 0;
        foreach ($targetProducts as $product) {
            $preprocessProductImage = $pIC->index($product->productImage);
            if (!$preprocessProductImage['status']) {
                DB::table('collected_products')->where('id', $product->id)->update([
                    'isActive' => 'N',
                    'remark' => 'Fail to load image',
                    'updatedAt' => now()
                ]);
                continue;
            }
            $newImageHref = $preprocessProductImage['return'];
            $preprocessProductDetail = $pIC->preprocessProductDetail($product);
            if (!$preprocessProductDetail['status']) {
                DB::table('collected_products')->where('id', $product->id)->update([
                    'isActive' => 'N',
                    'remark' => 'Fail to load image',
                    'updatedAt' => now()
                ]);
                continue;
            }
            $newProductDetail = $preprocessProductDetail['return'];
            DB::table('uploaded_products')
                ->insert([
                    'productId' => $product->id,
                    'userId' => $userId,
                    'newImageHref' => $newImageHref,
                    'newProductDetail' => $newProductDetail,
                    'newProductName' => $this->editProductName($product->productName)
                ]);
            if ($cnt == 0) {
                $startProductID = DB::getPdo()->lastInsertId();
                $cnt++;
            }
        }
        $preprocessedProducts = $this->preprocessedProducts($startProductID);
        return $preprocessedProducts;
    }
    public function preprocessedProducts($fromID)
    {
        $preprocessedProducts = DB::table('uploaded_products')
            ->join('collected_products', 'collected_products.id', '=', 'uploaded_products.productId')
            ->where('uploaded_products.productId', '>=', $fromID)
            ->where('uploaded_products.isActive', 'Y')
            ->limit(500)
            ->get();
        return $preprocessedProducts;
    }
    public function domeggook($products, $userId)
    {
        try {
            $margin_rate = 1.25;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 2;
            $minAmount = 5000;
            $categoryMappingController = new CategoryMappingController();
            foreach ($products as $product) {
                $product->newProductName = $this->editProductName($product->productName);
                $product_price = ceil($product->productPrice * $margin_rate);
                $minQuantity = ceil($minAmount / $product->productPrice);
                $categoryId = $product->categoryId;
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
                    $product->id,
                    $product->newImageHref,
                    $product->newProductDetail,
                    '',
                    '',
                    '',
                    'Y',
                    '',
                    40,
                    '전체상세정보별도표시',
                    '전체상세정보별도표시',
                    'N',
                    $minQuantity . ':' . $product_price,
                    '',
                    'N',
                    'N',
                    '1:' . $product_price,
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
    public function domeatoz($products, $userId)
    {
        try {
            $margin_rate = 1.15;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 3;
            $minAmount = 5000;
            $categoryMappingController = new CategoryMappingController();
            foreach ($products as $product) {
                $product->newProductName = $this->editProductName($product->productName);
                $product_price = ceil($product->productPrice * $margin_rate);
                $minQuantity = ceil($minAmount / $product->productPrice);
                $categoryId = $product->categoryId;
                $categoryCode = $categoryMappingController->domeatozCategoryCode($categoryId);
                $data = [
                    $categoryCode,
                    '',
                    '',
                    $product->newProductName,
                    '',
                    $product->newProductName,
                    $product->keywords,
                    '',
                    $product_price,
                    '',
                    0,
                    '배송비선불',
                    '',
                    $product->shippingCost,
                    $product->shippingCost,
                    0,
                    0,
                    $product->productVendor,
                    '기타',
                    '',
                    $product->newImageHref,
                    768,
                    'Y',
                    $product->newProductDetail,
                    '',
                    '',
                    0,
                    '',
                    '',
                    $product->id,
                    '',
                    35,
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기',
                    '상세정보별도표기'
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
            $fileName = 'domeatoz_' . $username . '_' . now()->format('YmdHis') . '.xlsx';
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
    public function wholesaledepot($products, $userId)
    {
        try {
            $margin_rate = 1.15;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 5;
            foreach ($products as $product) {
                $product->newProductName = $this->editProductName($product->productName);
                $product_price = ceil($product->productPrice * $margin_rate);
                $categoryId = $product->categoryId;
                $categoryMappingController = new CategoryMappingController();
                $categoryCode = $categoryMappingController->wsConvertCategoryCode($categoryId);
                $data = [
                    $product->newProductName,
                    $product->newProductName,
                    $categoryCode,
                    '',
                    $product->id,
                    '기타',
                    $product->productVendor,
                    $product->productVendor,
                    1,
                    0,
                    '',
                    0,
                    $product->keywords,
                    $product_price,
                    '',
                    0,
                    0,
                    $product->newProductDetail,
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
            $margin_rate = 1.15;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $product->newProductName = $this->editProductName($product->productName);
                $product_price = ceil($product->productPrice * $margin_rate);
                $categoryId = $product->categoryId;
                $categoryCode = DB::table('category')->where('id', $categoryId)->select('domesinCode')->first()->domesinCode;
                $data = [
                    '',
                    $product->newProductName,
                    $categoryCode,
                    $product->newProductName,
                    $product->id,
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
                    $product_price,
                    '',
                    '',
                    $product->newProductDetail,
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
            $margin_rate = 1.15;
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/ownerclan.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $product_price = ceil($product->productPrice * $margin_rate);
                $categoryId = $product->categoryId;
                $ownerclanCategoryCode = DB::table('category')->where('id', $categoryId)->select('code')->first()->code;
                $product->newProductName = $this->editProductName($product->productName);
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
                    $product_price,
                    '자율',
                    '',
                    '과세',
                    '',
                    '',
                    '',
                    'N,LADAM,' . $product->id,
                    $product->newImageHref,
                    '',
                    $product->newProductDetail,
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