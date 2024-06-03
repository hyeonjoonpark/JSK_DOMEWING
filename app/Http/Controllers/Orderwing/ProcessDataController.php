<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ProcessDataController extends Controller
{
    public function sellingkok($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'Q' => 'receiverName',
            'T' => 'receiverPhone',
            'R' => 'postcode',
            'S' => 'address',
            'H' => 'productName',
            'M' => 'quantity',
            'L' => 'productPrice',
            'N' => 'shippingCost',
            'A' => 'orderCode',
            'V' => 'shippingRemark',
            'G' => 'productCode',
            'E' => 'orderedAt',
            'P' => 'amount'
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [
                'senderName' => '',
                'senderPhone' => ''
            ];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['orderStatus'] = '신규주문';
            $rowData['b2BName'] = "셀링콕";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function ownerclan($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'B' => 'senderName',
            'D' => 'senderPhone',
            'E' => 'receiverName',
            'F' => 'receiverPhone',
            'H' => 'postcode',
            'I' => 'address',
            'J' => 'productName',
            'L' => 'quantity',
            'M' => 'productPrice',
            'N' => 'shippingCost',
            'P' => 'orderCode',
            'S' => 'shippingRemark',
            'V' => 'productCode',
            'W' => 'productCodeConditional', // Special handling for LADAM
            'A' => 'orderedAt',
            'O' => 'amount'
            // ... Add more mappings if needed
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['orderStatus'] = '배송준비';
            $rowData['b2BName'] = "오너클랜";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function domeatoz($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'C' => 'senderName',
            'D' => 'senderPhone',
            'E' => 'receiverName',
            'G' => 'receiverPhone',
            'H' => 'postcode',
            'I' => 'address',
            'M' => 'productName',
            'Q' => 'quantity',
            'R' => 'productPrice',
            'S' => 'shippingCost',
            'U' => 'orderCode',
            'W' => 'shippingRemark',
            'Y' => 'productCode',
            'Z' => 'productCodeConditional', // Special handling for LADAM
            'B' => 'orderedAt',
            'T' => 'amount',
            'A' => 'orderStatus'
            // ... Add more mappings if needed
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $rowData['amount'] = $rowData['productPrice'] * $rowData['quantity'] + $rowData['shippingCost'];
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "도매아토즈";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function wholesaledepot($excelPath)
    {
        // Create a reader for Xls format
        $reader = IOFactory::createReader('Csv');
        $reader->setInputEncoding('EUC-KR'); // EUC-KR encoding setting
        $spreadsheet = $reader->load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];
        $isFirstRow = true; // Flag to skip the first row

        // Define the column mappings for your data
        $columnMappings = [
            'A' => 'orderCode',
            'D' => 'receiverName',
            'F' => 'receiverPhone',
            'G' => 'postcode',
            'H' => 'address',
            'K' => 'productName',
            'R' => 'productPrice',
            'S' => 'quantity',
            'U' => 'shippingCost',
            'J' => 'productCode',
            'Y' => 'orderedAt',
            'AB' => 'shippingRemark',
            'X' => 'amount',
            'Z' => 'orderStatus'
            // Add more mappings as required
        ];

        // Loop through each row of the worksheet
        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue; // Skip the header row
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells

            $rowData = []; // Initialize array to store the cell data
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if (in_array($columnMappings[$columnLetter], ['productPrice', 'shippingCost', 'amount'])) {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }

            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "도매창고";

            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function domeggook($excelPath)
    {
        $reader = IOFactory::createReader('Csv');
        $reader->setInputEncoding('EUC-KR'); // EUC-KR 인코딩 설정
        $spreadsheet = $reader->load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true; // 첫 행은 헤더로 간주, 건너뛰기 위한 플래그

        // 열 매핑
        $columnMappings = [
            'L' => 'senderName',
            'M' => 'senderPhone',
            'N' => 'receiverName',
            'R' => 'receiverPhone',
            'P' => 'postcode',
            'O' => 'address',
            'G' => 'productName',
            'J' => 'quantity',
            'AM' => 'productPrice',
            'AA' => 'shippingCost',
            'A' => 'orderCode',
            'U' => 'shippingRemark',
            'I' => 'productCode',
            'AH' => 'orderedAt',
            'AN' => 'amount',
            'B' => 'orderStatus'
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // 모든 셀 순회

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    $value = $cell->getValue();
                    if ($columnLetter == 'P') {
                        $value = str_replace("'", "", $value);
                    }
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    if ($columnMappings[$columnLetter] == 'amount') {
                        (int)$value = (int)$value + (int)$rowData['shippingCost'];
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }

            $productCode = $rowData['productCode'] ?? '';
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "도매꾹";

            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function domeggook2($excelPath)
    {
        $reader = IOFactory::createReader('Csv');
        $reader->setInputEncoding('EUC-KR'); // EUC-KR 인코딩 설정
        $spreadsheet = $reader->load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true; // 첫 행은 헤더로 간주, 건너뛰기 위한 플래그

        // 열 매핑
        $columnMappings = [
            'L' => 'senderName',
            'M' => 'senderPhone',
            'N' => 'receiverName',
            'R' => 'receiverPhone',
            'P' => 'postcode',
            'O' => 'address',
            'G' => 'productName',
            'J' => 'quantity',
            'AM' => 'productPrice',
            'AA' => 'shippingCost',
            'A' => 'orderCode',
            'U' => 'shippingRemark',
            'I' => 'productCode',
            'AH' => 'orderedAt',
            'AN' => 'amount',
            'B' => 'orderStatus'
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // 모든 셀 순회

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    $value = $cell->getValue();
                    if ($columnLetter == 'P') {
                        $value = str_replace("'", "", $value);
                    }
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    if ($columnMappings[$columnLetter] == 'amount') {
                        (int)$value = (int)$value + (int)$rowData['shippingCost'];
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }

            $productCode = $rowData['productCode'] ?? '';
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "도매꾹2";

            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function domesin($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true; // Flag to skip the first row

        // Define the column mappings for your data
        $columnMappings = [
            'D' => 'receiverName',
            'E' => 'receiverPhone',
            'G' => 'postcode',
            'H' => 'address',
            'K' => 'productName',
            'S' => 'quantity',
            'R' => 'productPrice',
            'U' => 'shippingCost',
            'A' => 'orderCode',
            'Y' => 'shippingRemark',
            'J' => 'productCode',
            'V' => 'orderedAt',
            'W' => 'orderStatus'
            // Add more mappings as required
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false; // Skip the header row
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells

            $rowData = [
                'senderName' => '도매의신', // default value
                'senderPhone' => '' // default value
            ]; // Initialize array to store the cell data

            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['amount'] = (int)$rowData['productPrice'] * (int)$rowData['quantity'] + (int)$rowData['shippingCost'];
            $rowData['b2BName'] = "도매의신";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data; // Return the extracted data
    }
    public function domero($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'D' => 'receiverName',
            'E' => 'receiverPhone',
            'G' => 'postcode',
            'H' => 'address',
            'K' => 'productName',
            'R' => 'quantity',
            'Q' => 'productPrice',
            'T' => 'shippingCost',
            'A' => 'orderCode',
            'X' => 'shippingRemark',
            'J' => 'productCode',
            'U' => 'orderedAt',
            // 'O' => 'amount',
            'V' => 'orderStatus'
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [
                'senderName' => '', // default value
                'senderPhone' => '' // default value
            ]; // Initialize array to store the cell data

            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $quantity = (int) $rowData['quantity'];
            $shippingCost = (int) $rowData['shippingCost'];
            $rowData['amount'] = (int) $rowData['productPrice'] * $quantity + $shippingCost;
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['orderStatus'] = '배송준비';
            $rowData['b2BName'] = "도매로";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function specialoffer($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'A' => 'senderName',
            'P' => 'receiverName',
            'R' => 'receiverPhone',
            'S' => 'postcode',
            'T' => 'address',
            'F' => 'productName',
            'I' => 'quantity',
            'H' => 'productPrice',
            'K' => 'shippingCost',
            'B' => 'orderCode',
            'U' => 'shippingRemark',
            'E' => 'productCode',
            'AK' => 'productCodeConditional', // Special handling for LADAM
            'Y' => 'orderedAt',
            'L' => 'amount',
            'N' => 'orderStatus'
            // ... Add more mappings if needed
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "스페셜오퍼";
            $rowData['senderPhone'] = '';
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function tobizon($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'E' => 'receiverName', //수취인이름
            'F' => 'receiverPhone', //수취인 연락처
            'H' => 'postcode', //우편번호
            'I' => 'address', //주소
            'M' => 'productName', //상품이름
            'T' => 'quantity', //상품개수
            'U' => 'productPrice', //상품가격
            'X' => 'shippingCost', //배송비
            'A' => 'orderCode', //주문번호
            'J' => 'shippingRemark', //배송요청사항
            'L' => 'productCode', //우리꺼 상품코드
            'AA' => 'orderedAt', //주문한시간
            'Z' => 'amount', //총가격
            'AB' => 'orderStatus'
            // ... Add more mappings if needed
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);


            $rowData = [
                'senderName' => '',
                'senderPhone' => ''
            ];


            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "투비즈온";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function kseller($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'E' => 'receiverName',
            'F' => 'receiverPhone',
            'H' => 'postcode',
            'I' => 'address',
            'L' => 'productName',
            'S' => 'quantity',
            'R' => 'productPrice',
            'U' => 'shippingCost',
            'A' => 'orderCode',
            'Y' => 'shippingRemark',
            'K' => 'productCode',
            'V' => 'orderedAt',
            'W' => 'orderStatus'
            // ... Add more mappings if needed
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [
                'senderName' => '',
                'senderPhone' => ''
            ];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $productPrice = (int)$rowData['productPrice'];
            $quantity = (int)$rowData['quantity'];
            $productShippingFee = DB::table('minewing_products AS mp')
                ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
                ->where('mp.productCode', $productCode)
                ->first(['ps.shipping_fee'])
                ->shipping_fee;
            $rowData['shippingCost'] = $productShippingFee;
            $amount = $productPrice * $quantity;
            $rowData['amount'] = $amount;
            $rowData['b2BName'] = "K셀러";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function onch3($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'I' => 'receiverName',
            'J' => 'receiverPhone',
            'L' => 'postcode',
            'M' => 'address',
            'C' => 'productName',
            'F' => 'quantity',
            'G' => 'productPrice',
            'A' => 'orderCode',
            'N' => 'shippingRemark',
            'O' => 'productCode',
            'B' => 'orderedAt',
            // ... Add more mappings if needed
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [
                'senderName' => '',
                'senderPhone' => ''
            ];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    // Extract value and convert it to UTF-8
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            $productShippingFee = DB::table('minewing_products AS mp')
                ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
                ->where('mp.productCode', $productCode)
                ->first(['ps.shipping_fee'])
                ->shipping_fee;
            $rowData['shippingCost'] = $productShippingFee;
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['amount'] = (int)$rowData['productPrice'] * (int)$rowData['quantity'] + (int)$rowData['shippingCost'];
            $rowData['orderStatus'] = '배송준비';
            $rowData['b2BName'] = "온채널";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function funn($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getSheet(0);
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'E' => 'senderName',
            'F' => 'receiverName',
            'H' => 'receiverPhone',
            'R' => 'postcode',
            'S' => 'address',
            'J' => 'productName',
            'L' => 'quantity',
            'M' => 'productPrice',
            'P' => 'shippingCost',
            'A' => 'orderCode',
            'V' => 'productCode',
            'Q' => 'orderedAt',
            'O' => 'amount',
            'B' => 'orderStatus'
        ];

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [
                'senderPhone' => '',
                'shippingRemark' => ''
            ];
            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if ($columnLetter == 'T') {
                    $additionalTextForAddress = $cell->getValue();
                    continue;
                }
                if (isset($columnMappings[$columnLetter])) {
                    $value = $cell->getValue();

                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }

                    if ($columnMappings[$columnLetter] == 'productCode') {
                        $value = str_replace('JSKR', '', $value);
                    }

                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            if (isset($rowData['address']) && isset($additionalTextForAddress)) {
                $rowData['address'] .= " " . $additionalTextForAddress;
            }

            if (isset($rowData['amount']) && isset($rowData['shippingCost'])) {
                $rowData['amount'] = (int)$rowData['productPrice'] * (int)$rowData['quantity'] + (int)$rowData['shippingCost'];
            }

            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            if ($response['status'] === true) {
                $product = $response['return'];
                $rowData['productHref'] = $product->productHref;
                $rowData['productImage'] = $product->productImage;
            } else {
                $rowData['productName'] .= ' = (품절 상품)';
            }
            $rowData['b2BName'] = "펀앤";
            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
    public function trendhunterb2b()
    {
        $excelPath = public_path("assets/excel/orderwing/trendhunterb2b/");
        if (!is_dir($excelPath)) {
            mkdir($excelPath, 0755, true);
        }
        $allFiles = scandir($excelPath, SCANDIR_SORT_DESCENDING);

        $files = array_filter($allFiles, function ($file) use ($excelPath) {
            return is_file($excelPath . $file);
        });
        if (empty($files)) {
            echo "No Excel files found in the directory.";
            return;
        }
        $invoicesFiles = $excelPath . $files[0];
        $ordersFiles = $excelPath . $files[1];
        $ordersData = [];
        $invoicesData = [];


        // 두 번째 파일 (주문서)
        if (!empty($invoicesFiles)) {
            $ordersData = $this->processExcelFile($invoicesFiles, [
                'J' => 'quantity',
                'K' => 'productPrice',
                'M' => 'shippingCost',
                'N' => 'amount',
                'S' => 'productCode'
            ]);
        }

        // 첫 번째 파일 (발주서)
        if (!empty($ordersFiles)) {
            $invoicesData = $this->processExcelFile($ordersFiles, [
                'C' => 'receiverName',
                'D' => 'receiverPhone',
                'E' => 'postcode',
                'F' => 'address',
                'I' => 'productName',
                'H' => 'orderCode',
                'Q' => 'shippingRemark',
                'G' => 'orderedAt'
            ]);
        }
        $finalData = [];
        if (!empty($ordersData) && !empty($invoicesData)) {
            for ($i = 0; $i < count($ordersData); $i++) {
                if (isset($invoicesData[$i])) {
                    $finalData[] = array_merge($ordersData[$i], $invoicesData[$i]);
                }
            }
        }
        gc_collect_cycles();
        return $finalData;
    }

    private function processExcelFile($excelPath, $columnMappings)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        foreach ($worksheet->getRowIterator() as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];

            foreach ($cellIterator as $cell) {
                $columnLetter = $cell->getColumn();
                if (isset($columnMappings[$columnLetter])) {
                    $value = $cell->getValue();
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            if (isset($rowData['productCode'])) {
                $extractOrderController = new ExtractOrderController();
                $response = $extractOrderController->getProductHref($rowData['productCode']);
                if ($response['status'] === true) {
                    $product = $response['return'];
                    $rowData['productHref'] = $product->productHref;
                    $rowData['productImage'] = $product->productImage;
                }
                $rowData['orderStatus'] = '배송준비';
                $rowData['b2BName'] = "트렌드헌터";
            }

            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_collect_cycles();
        return $data;
    }
}
