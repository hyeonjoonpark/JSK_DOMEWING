<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoupangController extends Controller
{
    public function accountSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'expiredAt' => 'required|date',
            'accessKey' => 'required',
            'secretKey' => 'required'
        ], [
            'code' => '업체코드를 입력해주세요.',
            'expiredAt' => 'API 키 만료일을 기입해주세요.',
            'accessKey' => '엑세스 키를 기입해주세요.',
            'secretKey' => '시크릿 키를 기입해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
    }
    private function updateAccount($code, $expiredAt, $accessKey, $secretKey, $apiToken)
    {
        try {
            DB::table('coupang_accounts AS ca')
                ->join('');
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'DB 에 연동하는 과정에서 오류가 발생했습니다.',
                'data' => $e->getMessage()
            ];
        }
    }
}
