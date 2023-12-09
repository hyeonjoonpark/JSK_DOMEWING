<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Facades\DB;
use Exception;

class FormProductController extends Controller
{
    public function index()
    {
        $data = [];
        $fromUPID = 4398;
        $products = DB::table('uploaded_products')
            ->join('collected_products', 'collected_products.id', '=', 'uploaded_products.productId')
            ->where('uploaded_products.id', '>=', $fromUPID)
            ->limit(500)
            ->get();
        $vendors = DB::table('product_register')
            ->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')
            ->where('vendors.is_active', 'ACTIVE')
            ->where('product_register.is_active', 'Y')
            ->get();
        $userID = 15;
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        foreach ($vendors as $vendor) {
            $vendorEngName = $vendor->name_eng;
            $margin_rate = DB::table('margin_rate')
                ->where('vendorID', $vendor->id)
                ->first()
                ->rate;
            $margin_rate = (100 + $margin_rate) / 100;
            $response = $this->$vendorEngName($products, $userID, $margin_rate, $vendor->id);
            if ($response['status'] == 1) {
                $data['return']['successVendors'][] = $vendor->name;
                $data['return']['successVendorsNameEng'][] = $vendorEngName;
                $data['return']['formedExcelFiles'][] = $response['return'];
            }
        }
        return $data;
    }
    public function domeggook($products, $userId, $margin_rate)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 2;
            $minAmount = 5000;
            $categoryMappingController = new CategoryMappingController();
            foreach ($products as $product) {
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
    public function domeatoz($products, $userId, $margin_rate)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeatoz.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 3;
            $minAmount = 5000;
            $categoryMappingController = new CategoryMappingController();
            foreach ($products as $product) {
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
    public function wholesaledepot($products, $userId, $margin_rate)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 5;
            foreach ($products as $product) {
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

    public function domesin($products, $userId, $margin_rate, $vendorID)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                if ($vendorID === 6 && $product->sellerID === 3) {
                    continue;
                }
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
    public function ownerclan($products, $userId, $margin_rate)
    {
        try {
            // 엑셀 파일 로드
            $spreadsheet = IOFactory::load(public_path('assets/excel/ownerclan.xlsx'));
            $sheet = $spreadsheet->getSheet(0);
            // 데이터 추가
            $rowIndex = 4;
            foreach ($products as $product) {
                $product_price = ceil($product->productPrice * $margin_rate);
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
}
