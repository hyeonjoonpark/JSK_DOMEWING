<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ProcessDataController extends Controller
{
    public function ownerclan($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true;

        $columnMappings = [
            'B' => 'senderName',
            'C' => 'senderPhone',
            'E' => 'receiverName',
            'G' => 'receiverPhone',
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
            $rowData['productHref'] = $response->productHref;
            $rowData['productImage'] = $response->productImage;
            $rowData['orderStatus'] = '배송준비';
            $rowData['b2BName'] = "오너클랜";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }

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
            'P' => 'quantity',
            'Q' => 'productPrice',
            'R' => 'shippingCost',
            'T' => 'orderCode',
            'V' => 'shippingRemark',
            'X' => 'productCode',
            'Y' => 'productCodeConditional', // Special handling for LADAM
            'B' => 'orderedAt',
            'S' => 'amount',
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
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            $rowData['productHref'] = $response->productHref;
            $rowData['productImage'] = $response->productImage;
            $rowData['b2BName'] = "도매아토즈";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }

        return $data;
    }
    public function wholesaledepot($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
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
                    if ($columnMappings[$columnLetter] == 'productPrice' || $columnMappings[$columnLetter] == 'shippingCost' || $columnMappings[$columnLetter] == 'amount') {
                        $value = preg_replace('/[^0-9]/', '', $value);
                    }
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $response = $extractOrderController->getProductHref($productCode);
            $rowData['productHref'] = $response->productHref;
            $rowData['productImage'] = $response->productImage;
            $rowData['b2BName'] = "도매창고";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }

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
            $rowData['productHref'] = $response->productHref;
            $rowData['productImage'] = $response->productImage;
            $rowData['b2BName'] = "도매꾹";

            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }

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
            $rowData['productHref'] = $response->productHref;
            $rowData['productImage'] = $response->productImage;
            (int)$rowData['amount'] = (int)$rowData['productPrice'] * (int)$rowData['quantity'] + (int)$rowData['shippingCost'];
            $rowData['b2BName'] = "도매의신";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        return $data; // Return the extracted data
    }
}
