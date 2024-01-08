<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessDataController extends Controller
{
    public function safeUtf8Encode($value, $sourceEncoding = 'auto')
    {
        return mb_convert_encoding($value, 'UTF-8', $sourceEncoding);
    }
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
                    $value = $this->safeUtf8Encode($cell->getValue());
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $rowData['productHref'] = $extractOrderController->getProductHref($productCode);
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
            'F' => 'receiverPhone',
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
                    $value = $this->safeUtf8Encode($cell->getValue());
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
                    $value = $this->safeUtf8Encode($cell->getValue());
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $rowData['productHref'] = $extractOrderController->getProductHref($productCode);
            $rowData['b2BName'] = "도매창고";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }

        return $data;
    }
    public function domeggook($excelPath)
    {
        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $isFirstRow = true; // Flag to skip the first row

        // Define the column mappings for your data
        $columnMappings = [
            'L' => 'senderName',
            'M' => 'senderPhone',
            'N' => 'receiverName',
            'R' => 'receiverPhone',
            'P' => 'postcode',
            'O' => 'address',
            'G' => 'productName',
            'AL' => 'quantity',
            'AM' => 'productPrice',
            'AA' => 'shippingCost',
            'A' => 'orderCode',
            'U' => 'shippingRemark',
            'I' => 'productCode',
            'AH' => 'orderedAt',
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
                    $value = $this->safeUtf8Encode($cell->getValue());
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $rowData['productHref'] = $extractOrderController->getProductHref($productCode);
            $rowData['b2BName'] = "도매꾹";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
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
                    $value = $this->safeUtf8Encode($cell->getValue());
                    $rowData[$columnMappings[$columnLetter]] = $value;
                }
            }
            $productCode = $rowData['productCode'];
            $extractOrderController = new ExtractOrderController();
            $rowData['productHref'] = $extractOrderController->getProductHref($productCode);
            $rowData['b2BName'] = "도매의신";
            if (!empty($rowData)) {
                $data[] = $rowData; // Push the row data to the main data array if not empty
            }
        }
        return $data; // Return the extracted data
    }
}
