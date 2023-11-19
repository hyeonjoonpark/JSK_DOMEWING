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
        $collectedProducts = DB::select("
            SELECT cp.*
            FROM collected_products cp
            LEFT JOIN uploaded_products up ON up.productId = cp.id
            WHERE up.productId IS NULL;
        ");
        $pIC = new ProductImageController();
        set_time_limit(0);
        foreach ($collectedProducts as $collectedProduct) {
            $collectedProduct->newImageHref = $pIC->index($collectedProduct->productImage);
            if ($collectedProduct->newImageHref == false) {
                DB::table('collected_products')->where('id', $collectedProduct->id)->update([
                    'isActive' => 'N'
                ]);
            }
            $collectedProduct->newProductName = $this->editProductName($collectedProduct->productName);
        }
        $userId = DB::table('users')->where('remember_token', $request->remember_token)->first()->id;
        $data = $this->ownerclan($collectedProducts, $userId);
        return $data;
    }
    public function domeggook(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            // 카테고리 코드 변환을 위한 컨트롤러 생성
            $categoryMappingController = new CategoryMappingController();
            $categoryCode = $categoryMappingController->domeggookCategoryCode($categoryCode);

            // 엑셀 파일 불러오기
            $spreadsheet = IOFactory::load(public_path('assets/excel/domeggook.xls'));
            $sheet = $spreadsheet->getSheet(0);

            if ($request->shipping == '선불') {
                $shipPolicy = '선결제';
            }
            if ($request->shipping == '무료') {
                $shipPolicy = '구매자선택';
            }

            $saleToMinor = ($request->saleToMinor == '가능') ? 'N' : 'Y';

            $productInformationCode = DB::table('product_information')->where('domesin_value', $request->product_information)->select('domeggook_value')->first()->domeggok_value;
            $price = $request->price;
            $minQuantity = (5000 / $price) + 1;
            // 제품 데이터 배열 생성
            $dataset = [
                'productCode' => '',
                'saleChannel' => '도매꾹, 도매매',
                'saleType' => '직접판매',
                'isClassified' => 'N',
                'productName' => $request->itemName,
                'keywords' => $request->keywords,
                'categoryCode' => $categoryCode,
                'originated' => $request->origin,
                'model' => $request->model,
                'vendor' => $request->vendor,
                'safetyCertification' => 'N',
                'saleToMinor' => $saleToMinor,
                'volume' => '',
                'weight' => '',
                'supplierProductCode' => '',
                'productImage' => $productImage,
                'productDetail00' => $descImage,
                'productDetail01' => '',
                'productDetail02' => '',
                'productDetail03' => '',
                'detailUsePermitted' => 'Y',
                'additionalText' => '',
                'productInformationCode' => $productInformationCode,
                'productInformationDetail' => '전체상세정보별도표시',
                'tradeInformation' => '전체상세정보별도표시',
                'domeggookSaleType' => 'Y',
                'domeggookPrice' => $minQuantity . ':' . $price,
                'maxQuantity' => '',
                'multipleSale' => 'N',
                'nego' => 'N',
                'domemePrice' => '1:' . $price,
                'minPrice' => $request->price,
                'recomendPrice' => $request->price,
                'option' => 'N',
                'optionAdd' => '',
                'optionanother' => "N",
                'optionValue' => '',
                'optionPrice' => '',
                'optionPrice2' => '',
                'stockInitialPrice' => '',
                'stock' => '500',
                'taxabilitiy' => $request->taxability,
                'salerPoint' => '0',
                'deliveryMethod' => '택배',
                'internationalShipping' => 'N',
                'immigration' => '',
                'shipDuration' => '0',
                'shipPolicy' => $shipPolicy . ':고정배송비',
                'shipCost' => $request->shipCost,
                'domemeShipCost' => $request->shipCost,
                'bundleShipping' => '',
                'refundAddress' => 'SA0058243',
                'refundCost' => $request->shipCost,
                'openDays' => '365',
                'isDisplay' => 'Y',
            ];

            // 제품 정보를 엑셀에 추가
            $newRow = 2;
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }

            // 엑셀 파일 업로드
            $writer = new Xlsx($spreadsheet);
            $fileName = 'domeggook_' . $username . '_' . date('YmdHis') . '.xlsx';
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
    public function wholesaledepot(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            // 카테고리 코드 변환을 위한 컨트롤러 생성
            $categoryMappingController = new CategoryMappingController();
            $categoryCode = $categoryMappingController->wsConvertCategoryCode($categoryCode);

            // 엑셀 파일 불러오기
            $spreadsheet = IOFactory::load(public_path('assets/excel/wholesaledepot.xls'));
            $sheet = $spreadsheet->getSheet(0);

            // 배송비 설정
            if ($request->shipping == '무료') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }

            $taxability = ($request->taxability === '과세') ? 1 : 2;

            if ($request->shipping == '선불') {
                $shipping = 0;
            } elseif ($request->shipping == '착불') {
                $shipping = 3;
            } elseif ($request->shipping == '무료') {
                $shipping = 1;
            }

            $saleToMinor = ($request->saleToMinor == '가능') ? '2' : '1';

            $productInformationCode = DB::table('product_information')
                ->where('domesin_value', $request->product_information)
                ->select('wsd_value')
                ->first()
                ->wsd_value;

            // 제품 데이터 배열 생성
            $dataset = [
                'productName' => $request->itemName,
                'invoiceName' => $request->invoiceName,
                'categoryCode' => $categoryCode,
                'productCode' => '',
                'productCode2' => '',
                'originated' => $request->origin,
                'vendor' => $request->vendor,
                'brand' => $request->vendor,
                'taxability' => $taxability,
                'shipType' => $shipping,
                'bundledQuantity' => '0',
                'isInternationalShipping' => '0',
                'keywords' => $request->keywords,
                'productPrice' => $request->price,
                'forcedPrice' => '',
                'isForced' => '0',
                'isConsumerNotified' => '0',
                'descImage' => $descImage,
                'productImage' => $productImage,
                'extraImage1' => '',
                'extraImage2' => '',
                'extraImage3' => '',
                'extraImage4' => '',
                'extraImage5' => '',
                'isExclusive' => '0',
                'selectedOption' => '0',
                'inputOption' => '',
                'credentials' => 'C',
                'credentialCode' => '',
                'refundAddress' => '1597',
                'isRefundable' => '1',
                'refundReason' => '',
                'saleToMinor' => $saleToMinor,
                'isSale' => '0',
                'isDisplay' => '1',
                'isNew' => 'N',
                'productNotice' => '',
                'productInformationCode' => $productInformationCode,
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
            $newRow = 5;
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }

            // 엑셀 파일 업로드
            $writer = new Xls($spreadsheet);
            $fileName = 'wsd_' . $username . '_' . date('YmdHis') . '.xls';
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

    public function domesin(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        try {
            $spreadsheet = IOFactory::load(public_path('assets/excel/domesin.xls'));
            $sheet = $spreadsheet->getsheet(0);
            if ($request->shipping != '무료') {
                $shipCost = 0;
            } else {
                $shipCost = $request->shipCost;
            }
            $shipCost = $request->shipCost;
            // 데이터 배열 생성
            $domesinCode = DB::table('category')->where('code', $categoryCode)->select('domesinCode')->first();
            $domesinCode = $domesinCode->domesinCode;
            if ($request->taxability === '과세') {
                $taxability = 0;
            } else {
                $taxability = 1;
            }
            if ($request->shipping == '선불') {
                $shipping = 0;
            } else if ($request->shipping == '착불') {
                $shipping = 2;
            } else if ($request->shipping == '무료') {
                $shipping = 1;
            }
            if ($request->saleToMinor == '가능') {
                $saleToMinor = '';
            } else {
                $saleToMinor = '1';
            }
            $dataset = [
                'productCode' => '',
                'productName' => $request->itemName,
                'categoryCode' => $domesinCode,
                'invoiceName' => $request->invoiceName,
                'productCode2' => '',
                'originated' => $request->origin,
                'vendor' => $request->vendor,
                'brand' => $request->vendor,
                'model' => $request->model,
                'taxability' => $taxability,
                'shipType' => $shipping,
                'bundledQuantity' => '',
                'isInternationalShipping' => '',
                'shipCost' => $shipCost,
                'refundCost' => $shipCost,
                'refundAddress' => '1508',
                'keywords' => $request->keywords,
                'productPrice' => $request->price,
                'forcedPrice' => '',
                'normalPrice' => '',
                'descImage' => $descImage,
                'productImage' => $productImage,
                'extraImage1' => '',
                'extraImage2' => '',
                'extraImage3' => '',
                'extraImage4' => '',
                'extraImage5' => '',
                'isExclusive' => '0',
                'selectedOption' => '',
                'inputOption' => '',
                'credentials' => '0',
                'credentialCode' => '',
                'credentialNum' => '',
                'isRefundable' => '1',
                'saleToMinor' => $saleToMinor,
                'isSale' => '0',
                'isDisplay' => '1',
                'productCondition' => '',
                'productNotice' => '',
                'productInformationCode' => $request->product_information,
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
            // 추가할 새로운 행의 위치를 지정합니다.
            $newRow = 4; // 현재 데이터가 있는 가장 아래 행 다음에 추가하려면 +1을 사용합니다.
            $col = 'A';
            foreach ($dataset as $value) {
                $sheet->setCellValue($col . $newRow, $value);
                $col++;
            }
            // 엑셀 파일 업로드
            // 변경된 내용을 파일로 저장
            $writer = new Xls($spreadsheet);
            $fileName = $username . '_' . date('YmdHis') . '.xls';
            $formedExcelFile = public_path('assets/excel/formed/' . $fileName);
            $writer->save($formedExcelFile);
            $data['status'] = 1;
            $data['return'] = $fileName;
            return $data;
        } catch (Exception $e) {
            $data['status'] = -1;
            $data['return'] = $e->getMessage();
            return $data;
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
                    $this->editProductName($product->productName),
                    $this->editProductName($product->productName),
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
            if (!preg_match('/[가-힣0-9a-zA-Z ]/', $char)) {
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