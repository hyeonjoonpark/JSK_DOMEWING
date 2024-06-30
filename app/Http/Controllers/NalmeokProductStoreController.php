<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NalmeokProductStoreController extends Controller
{
    public function main(Request $request)
    {
        $validatorResult = $this->validator($request);
        if (!$validatorResult['status']) {
            return response()->json($validatorResult);
        }
        $extractProductsResult = $this->extractProducts($request->excel, $request->vendorId);
        if (!$extractProductsResult['status']) {
            return response()->json($extractProductsResult);
        }
    }
    protected function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendorId' => ['required', 'exists:vendors,id'],
            'excel' => ['required', 'file', 'mimes:xlsx,xls,csv']
        ], [
            'vendorId' => '유효한 B2B 업체를 선택해 주세요.',
            'excel.required' => '엑셀 파일을 업로드해 주세요.',
            'excel.file' => '업로드된 파일이 유효하지 않습니다.',
            'excel.mimes' => '엑셀 파일은 xlsx, xls, csv 형식이어야 합니다.'
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
    protected function extractProducts(UploadedFile $excel, int $vendorId)
    {
        $spreadsheet = IOFactory::load($excel->getPathname());
        $worksheet = $spreadsheet->getSheet(0);
        $highestRow = $worksheet->getHighestRow();
        $method = DB::table('vendors')
            ->where('id', $vendorId)
            ->value('name_eng');
        try {
            $methodResult = $this->$method($worksheet, $highestRow, $method);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '연동 준비 중인 업체입니다.',
                'error' => $e->getMessage()
            ];
        }
        if (!$methodResult['status']) {
            return $methodResult;
        }
    }
    protected function ownerclan(Worksheet $worksheet, int $highestRow, $vendorEngName)
    {
        $lowestRow = 3;
        $unmappedCategoryProducts = [];
        for ($i = $lowestRow; $i <= $highestRow; $i++) {
            $code = $worksheet->getCell('C' . $i)->getValue();
            $categoryCode = $worksheet->getCell('D' . $i)->getValue();
            $ownerclanCategoryId = $this->getOwnerclanCategoryId($categoryCode, $vendorEngName);
            if (!$ownerclanCategoryId) {
                $unmappedCategoryProducts[] = $code;
            }
        }
    }
    protected function getOwnerclanCategoryId(string $categoryCode, string $vendorEngName)
    {
        return DB::table('category_mapping AS cm')
            ->join($vendorEngName . '_category AS c', 'c.id', '=', 'cm.' . $vendorEngName)
            ->where('c.code', $categoryCode)
            ->value('cm.ownerclan');
    }
    protected function insertCategoryMapping()
    {
    }
    protected function insertNalmeokProduct($ownerclanCategoryId, $vendorId, $code, $name, $price, $shippingType, $shippingFee, $returnShippingFee, $image, $keywords, $detail, $options)
    {
        DB::beginTransaction();
        try {
            $nalmeokProductId = DB::table('nalmeok_products')
                ->insertGetId([
                    'ownerclan_category_id' => $ownerclanCategoryId,
                    'vendor_id' => $vendorId,
                    'code' => $code,
                    'name' => $name,
                    'price' => $price,
                    'shipping_type' => $shippingType,
                    'shipping_fee' => $shippingFee,
                    'return_shipping_fee' => $returnShippingFee,
                    'image' => $image,
                    'keywords' => $keywords,
                    'detail' => $detail
                ]);
            foreach ($options as $o) {
                DB::table('nalmeok_product_options')
                    ->insert([
                        'nalmeok_product_id' => $nalmeokProductId,
                        'name' => $o['name'],
                        'values' => $o['values']
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
