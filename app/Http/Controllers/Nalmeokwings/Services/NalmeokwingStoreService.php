<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NalmeokwingStoreService extends Controller
{
    public function main(Request $request)
    {
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
        return [
            'status' => true,
            'data' => $sheetData
        ];
    }
}
