<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NalmeokwingExtractService extends Controller
{
    public function main(Request $request)
    {
        $validator = $this->validator($request);
        if (!$validator['status']) {
            return $validator;
        }
        $getOrdersResult = $this->getOrders($request->input('vendorId'));
        if (!$getOrdersResult['status']) {
            return $getOrdersResult;
        }
        $orders = $getOrdersResult['data'];
        $vendorEngName = DB::table('vendors')
            ->where('id', $request->input('vendorId'))
            ->value('name_eng');
        $result = $this->$vendorEngName($orders);
    }
    protected function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorId' => ['required', 'integer', 'exists:vendors,id']
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => '유효한 B2B 업체를 선택해주세요.'
            ];
        }
        return [
            'status' => true
        ];
    }
    protected function getOrders(int $vendorId)
    {
        $orders = DB::table('orders AS o')
            ->join('carts AS c', 'c.id', '=', 'o.cart_id')
            ->join('minewing_products AS mp', 'mp.id', '=', 'c.product_id')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('v.id', $vendorId)
            ->where('o.delivery_status', 'PENDING')
            ->where('o.type', 'PAID')
            ->get();
        return [
            'status' => count($orders) > 0 ? true : false,
            'data' => $orders,
            'message' => '해당 B2B 업체로부터의 주문 내역이 없습니다.'
        ];
    }
    protected function ownerclan(Collection $orders)
    {
        $filename = public_path('assets/excel/nalmeokwings/forms/ownerclan.xlsx');
        $spreadsheet = IOFactory::load($filename);
        $sheet = $spreadsheet->getSheet(0);
        $rowNumber = 3;
        foreach ($orders as $order) {
            $option1Value = '';
            $option2Value = '';
            if ($order->hasOption === 'Y') {
                $productOptions = $this->processProductOptions($order->productDetail);
                $option1Value = $productOptions['option1Value'];
                $option2Value = $productOptions['option2Value'];
            }
            $sheet->setCellValue('A' . $rowNumber, $order->origin_product_code);
            $sheet->setCellValue('B' . $rowNumber, $order->quantity);
            $sheet->setCellValue('C' . $rowNumber, '선불');
            $sheet->setCellValue('D' . $rowNumber, $order->receiver_name);
            $sheet->setCellValue('E' . $rowNumber, $order->receiver_phone);
            $sheet->setCellValue('F' . $rowNumber, $order->receiver_phone);
            $sheet->setCellValue('G' . $rowNumber, '');
            $sheet->setCellValue('H' . $rowNumber, $order->receiver_address);
            $sheet->setCellValue('I' . $rowNumber, $option1Value);
            $sheet->setCellValue('J' . $rowNumber, $option2Value);
            $sheet->setCellValue('K' . $rowNumber, $order->receiver_remark);
            $rowNumber++;
        }
    }
    protected function processProductOptions(string $productDetail)
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($productDetail);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);
        $h1Nodes = $xpath->query('//h1');
        $text = $h1Nodes[0]->textContent;
        preg_match_all('/옵션: (.+?) - (.+?)(?: \/ (.+?) - (.+?))?/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $option1Value = $match[2] ?? '';
            $option2Value = $match[4] ?? '';
        }
        return [
            'option1Value' => $option1Value,
            'option2Value' => $option2Value
        ];
    }
}
