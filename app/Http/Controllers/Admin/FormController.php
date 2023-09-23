<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FormController extends Controller
{
    public function ownerclan(Request $request, $username, $password, $categoryCode, $productImage, $descImage)
    {
        $filePath = public_path('assets/excel/ownerclan.xlsx');
        $excel = Excel::load($filePath);

        // 시트 선택 (시트 이름 또는 인덱스 사용 가능)
        $sheet = $excel->getSheetByName('등록수정양식');



        // 엑셀 파일 저장
        $excel->store('xlsx', '경로/수정된_파일.xlsx');
        $medical = 0;
        if ($request->madicalEquipment == '의료기기') {
            $medical = 1;
        }
        if ($request->healthFunctional == '건강기능식품') {
            $medical = 2;
        }
        $data = [
            '',
            $categoryCode,
            '',
            '',
            $request->itemName,
            $request->invoiceName,
            $request->keywords,
            $request->origin,
            $request->vendor,
            $request->model,
            $request->price,
            '자율',
            '',
            $request->taxability,
            '',
            '',
            '',
            'N,,',
            asset('assets/images/product/') . $productImage,
            '',
            '<p align="center"><img src="' . asset('assets/images/product/desc') . $descImage . '"></p>',
            $request->saleToMinor,
            $request->shipping,
            $request->shipCost,
            $request->shipCost,
            '',
            '',
            '',
            '1',
            '0',
            '',
            '35',
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
            $medical,
            '',
            '',
            '',
            '',
            ''
        ];
        // 열 추가
        $sheet->appendRow($data);
        $data['status'] = 1;
        $data['return'] = 'success';
        return $data;
    }
}