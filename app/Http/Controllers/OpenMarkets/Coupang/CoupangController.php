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
        return $this->updateAccount($request->code, $request->expiredAt, $request->accessKey, $request->secretKey, $request->apiToken);
    }
    private function updateAccount($code, $expiredAt, $accessKey, $secretKey, $apiToken)
    {
        try {
            $partnerId = DB::table('partners')
                ->where('api_token', $apiToken)
                ->first(['id'])
                ->id;
            DB::table('coupang_accounts')
                ->where('partner_id', $partnerId)
                ->update([
                    'is_active' => 'INACTIVE'
                ]);
            DB::table('coupang_accounts')
                ->insert([
                    'partner_id' => $partnerId,
                    'code' => trim($code),
                    'expired_at' => $expiredAt . ' 23:59:59',
                    'access_key' => trim($accessKey),
                    'secret_key' => trim($secretKey)
                ]);
            return [
                'status' => true,
                'message' => '계정 정보를 성공적으로 추가했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'DB 에 연동하는 과정에서 오류가 발생했습니다.',
                'data' => $e->getMessage()
            ];
        }
    }
}
