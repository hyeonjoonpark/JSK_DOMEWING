<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LotteOnAccountController extends Controller
{
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accessKey' => ['required', 'max:255'],
            'username' => ['required', 'max:255']
        ], [
            'accessKey.required' => 'OPEN API KEY는 필수 항목입니다.',
            'accessKey.max' => 'OPEN API KEY는 최대 255자 이하로 입력해야 합니다.',
            'username.required' => '계정명 및 별칭은 필수 항목입니다.',
            'username.max' => '계정명 및 별칭은 최대 255자 이하로 입력해야 합니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $accessKey = $request->accessKey;
        $username = $request->username;
        $requestValidateApiKeyResult = $this->requestValidateApiKey($accessKey);
        if ($requestValidateApiKeyResult['status'] === false) {
            return $requestValidateApiKeyResult;
        }
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->value('id');
        return $this->store($partnerId, $username, $accessKey);
    }
    private function store($partnerId, $username, $accessKey)
    {
        try {
            DB::table('lotte_on_accounts')
                ->insert([
                    'partner_id' => $partnerId,
                    'username' => $username,
                    'access_key' => $accessKey,
                    'hash' => Str::uuid()
                ]);
            return [
                'status' => true,
                'message' => '롯데온 계정을 성공적으로 추가 연동했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'API 계정 정보를 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function requestValidateApiKey($accessKey)
    {
        $url = 'https://openapi.lotteon.com/v1/openapi/common/v1/identity';
        $ac = new LotteOnApiController();
        $builderResult = $ac->getBuilder($accessKey, $url);
        if (!isset($builderResult['httpcode']) || $builderResult['httpcode'] !== 200) {
            return [
                'status' => false,
                'message' => '올바르지 않은 인증키 입니다.',
                'error' => $builderResult
            ];
        }
        return [
            'status' => true,
            'data' => $builderResult
        ];
    }
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accessKey' => ['required', 'max:255'],
            'username' => ['required', 'max:255'],
            'hash' => ['required', 'exists:lotte_on_accounts,hash']
        ], [
            'accessKey.required' => 'OPEN API KEY는 필수 항목입니다.',
            'accessKey.max' => 'OPEN API KEY는 최대 255자 이하로 입력해야 합니다.',
            'username.required' => '계정명 및 별칭은 필수 항목입니다.',
            'username.max' => '계정명 및 별칭은 최대 255자 이하로 입력해야 합니다.',
            'hash' => '유효한 계정이 아닙니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $accessKey = $request->accessKey;
        $username = $request->username;
        $hash = $request->hash;
        $requestValidateApiKeyResult = $this->requestValidateApiKey($accessKey);
        if ($requestValidateApiKeyResult['status'] === false) {
            return $requestValidateApiKeyResult;
        }
        return $this->update($hash, $username, $accessKey);
    }
    private function update($hash, $username, $accessKey)
    {
        try {
            DB::table('lotte_on_accounts')
                ->where('hash', $hash)
                ->update([
                    'username' => $username,
                    'access_key' => $accessKey
                ]);
            return [
                'status' => true,
                'message' => '수정된 사항을 성공적으로 저장했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '수정된 사항을 저장하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hash' => ['required', 'exists:lotte_on_accounts,hash']
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => '유효한 계정이 아닙니다.'
            ];
        }
        $hash = $request->hash;
        return $this->destroy($hash);
    }
    private function destroy($hash)
    {
        try {
            DB::table('lotte_on_accounts')
                ->where('hash', $hash)
                ->update([
                    'is_active' => 'INACTIVE'
                ]);
            return [
                'status' => true,
                'message' => '해당 계정을 성공적으로 삭제했습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '해당 계정을 삭제하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
