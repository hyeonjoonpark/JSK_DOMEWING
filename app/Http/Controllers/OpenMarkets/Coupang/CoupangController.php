<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CoupangController extends Controller
{
    public function accountSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'expiredAt' => 'required|date',
            'accessKey' => 'required',
            'secretKey' => 'required',
            'name' => 'required|max:16'
        ], [
            'code' => '업체코드를 입력해주세요.',
            'expiredAt' => 'API 키 만료일을 기입해주세요.',
            'accessKey' => '엑세스 키를 기입해주세요.',
            'secretKey' => '시크릿 키를 기입해주세요.',
            'name.required' => '본 계정 별칭을 입력해주세요.',
            'name.max' => '별칭은 16글자 이하여야 합니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        return $this->updateAccount($request->code, $request->expiredAt, $request->accessKey, $request->secretKey, $request->apiToken, $request->name);
    }
    private function updateAccount($code, $expiredAt, $accessKey, $secretKey, $apiToken, $name)
    {
        try {
            $exists = DB::table('coupang_accounts')
                ->where('secret_key', trim($secretKey))
                ->exists();
            if ($exists === true) {
                return [
                    'status' => false,
                    'message' => '이미 추가된 계정입니다.'
                ];
            }
            $partnerId = DB::table('partners')
                ->where([
                    'api_token' => $apiToken,
                    'is_active' => 'ACTIVE'
                ])
                ->first(['id'])
                ->id;
            DB::table('coupang_accounts')
                ->insert([
                    'partner_id' => $partnerId,
                    'code' => trim($code),
                    'expired_at' => $expiredAt . ' 23:59:59',
                    'access_key' => trim($accessKey),
                    'secret_key' => trim($secretKey),
                    'name' => trim($name),
                    'hash' => Str::uuid()
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
