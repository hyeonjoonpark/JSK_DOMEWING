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
            'username' => 'required|max:16'
        ], [
            'code' => '업체코드를 입력해주세요.',
            'expiredAt' => 'API 키 만료일을 기입해주세요.',
            'accessKey' => '엑세스 키를 기입해주세요.',
            'secretKey' => '시크릿 키를 기입해주세요.',
            'username.required' => '본 계정 별칭을 입력해주세요.',
            'username.max' => '별칭은 16글자 이하여야 합니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $accessKey = $request->accessKey;
        $secretKey = $request->secretKey;
        $vendorId = $request->code;
        $validateAccountResult = $this->validateAccount($accessKey, $secretKey, $vendorId);
        if ($validateAccountResult['status'] === false) {
            return $validateAccountResult;
        }
        return $this->createAccount($request->code, $request->expiredAt, $request->accessKey, $request->secretKey, $request->apiToken, $request->username);
    }
    private function validateAccount($accessKey, $secretKey)
    {
        $apiController = new ApiController();
        $method = "GET";
        $path = "/v2/providers/seller_api/apis/api/v1/marketplace/meta/display-categories/0";
        $apiResult = $apiController->build($method, $path, $accessKey, $secretKey);
        $data = $apiResult['data'];
        $httpcode = $data['httpcode'];
        if ((int)$httpcode !== 200) {
            return [
                'status' => false,
                'message' => '유효한 계정 정보가 아닙니다.'
            ];
        }
        return [
            'status' => true
        ];
    }
    private function createAccount($code, $expiredAt, $accessKey, $secretKey, $apiToken, $username)
    {
        try {
            $exists = DB::table('coupang_accounts')
                ->where('secret_key', trim($secretKey))
                ->where('is_active', 'ACTIVE')
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
                    'username' => trim($username),
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
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'expiredAt' => 'required|date',
            'accessKey' => 'required',
            'secretKey' => 'required',
            'username' => 'required|max:16',
            'hash' => 'required'
        ], [
            'code' => '업체코드를 입력해주세요.',
            'expiredAt' => 'API 키 만료일을 기입해주세요.',
            'accessKey' => '엑세스 키를 기입해주세요.',
            'secretKey' => '시크릿 키를 기입해주세요.',
            'username.required' => '본 계정 별칭을 입력해주세요.',
            'username.max' => '별칭은 16글자 이하여야 합니다.',
            'hash' => '유효한 계정을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $hash = $request->hash;
        $code = $request->code;
        $expiredAt = $request->expiredAt;
        $accessKey = $request->accessKey;
        $secretKey = $request->secretKey;
        $username = $request->username;
        $validateAccountResult = $this->validateAccount($accessKey, $secretKey);
        if ($validateAccountResult['status'] === false) {
            return $validateAccountResult;
        }
        return $this->update($code, $expiredAt, $accessKey, $secretKey, $username, $hash);
    }
    private function update($code, $expiredAt, $accessKey, $secretKey, $username, $hash)
    {
        DB::table('coupang_accounts')
            ->where([
                'hash' => $hash
            ])
            ->update([
                'code' => $code,
                'username' => $username,
                'access_key' => $accessKey,
                'secret_key' => $secretKey,
                'expired_at' => $expiredAt
            ]);
        return [
            'status' => true,
            'message' => '쿠팡 계정 정보를 성공적으로 업데이트했습니다.'
        ];
    }
    public function delete(Request $request)
    {
        $hash = $request->hash;
        try {
            DB::table('coupang_accounts')
                ->where('hash', $hash)
                ->update([
                    'is_active' => 'INACTIVE'
                ]);
            return [
                'status' => true,
                'message' => "해당 계정을 성공적으로 삭제했습니다."
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => "계정 정보를 삭제하는 과정에서 오류가 발생했습니다.",
                'error' => $e->getMessage()
            ];
        }
    }
}
