<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryMappingController extends Controller
{
    public function index(Request $request)
    {
        $vendorID = $request->vendorID;
        $keyword = $request->keyword;
        // 검증 규칙 설정
        $validator = Validator::make($request->all(), [
            'keyword' => ['required', 'min:2', 'max:20'] // 키워드 필드에 대한 유효성 검사 규칙 설정
        ], [
            'keyword' => '검색어는 2자 이상 20자 이하로 기입해주세요.' // 유효성 검사 실패 시 반환할 오류 메시지 설정
        ]);
        // 유효성 검사 실패 시 오류 반환
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first()); // 유효성 검사 실패 시 오류 메시지 반환
        }
        $vendorEngName = DB::table('vendors')->where('id', $vendorID)->select('name_eng')->first()->name_eng;
        $categories = DB::table($vendorEngName . '_category')->where('name', 'LIKE', '%' . $keyword . '%')->get(); // 카테고리 테이블에서 키워드를 포함하는 카테고리 검색
        if (!empty($categories)) {
            return $this->getResponseData(1, $categories); // 검색 결과가 있을 경우 결과 반환
        } else {
            return $this->getResponseData(-1, '검색 결과가 없습니다. 다른 키워드로 검색해주세요.'); // 검색 결과가 없을 경우 오류 메시지 반환
        }
    }
    // 응답 데이터 생성
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
