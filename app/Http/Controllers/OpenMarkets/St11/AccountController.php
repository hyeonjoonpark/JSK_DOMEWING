<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountController extends Controller
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
            DB::table('st11_accounts')
                ->insert([
                    'partner_id' => $partnerId,
                    'username' => $username,
                    'access_key' => $accessKey,
                    'hash' => Str::uuid()
                ]);
            return [
                'status' => true,
                'message' => '11번가 계정을 성공적으로 추가 연동했습니다.'
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
        $data = [
            'key' => $accessKey,
            'apiCode' => 'ProductSearch',
            'keyword' => 'test'
        ];
        $method = 'get';
        $url = 'https://openapi.11st.co.kr/openapi/OpenApiService.tmall';
        $ac = new ApiController();
        $builderResult = $ac->builder($accessKey, $method, $url, $data);
        if ($builderResult['status'] === false) {
            return $builderResult;
        }
        if (!isset($builderResult['data']->Products->TotalCount)) {
            return [
                'status' => false,
                'message' => '등록되지 않은 OpenAPI Key 입니다.'
            ];
        }
        return [
            'status' => true
        ];
    }
}
