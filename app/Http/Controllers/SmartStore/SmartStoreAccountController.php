<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmartStoreAccountController extends Controller
{
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'applicationId' => 'required',
            'secret' => 'required',
            'storeName' => 'required'
        ], [
            'username' => '네이버 커머스 판매자 로그인 아이디를 기입해주세요.',
            'applicationId' => '애플리케이션 ID를 입력해주세요.',
            'secret' => '애플리케이션 시크릿 코드를 입력해주세요.',
            'storeName' => '스토어명을 입력해주세요.',
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $username = trim($request->username);
        $applicationId = trim($request->applicationId);
        $secret = trim($request->secret);
        $storeName = trim($request->storeName);
        $getAccessTokenResult = $this->getAccessToken($applicationId, $secret, $username);
        if ($getAccessTokenResult['status'] === false) {
            return [
                'status' => false,
                'message' => '유효한 API 계정 정보가 아닙니다.'
            ];
        }
        $getAccessTokenData = $getAccessTokenResult['data'];
        $accessToken = $getAccessTokenData->access_token;
        $expiresIn = $getAccessTokenData->expires_in;
        $now = new DateTime();
        $secondsToAdd = new DateInterval('PT' . $expiresIn . 'S');
        $now->add($secondsToAdd);
        $expiredAt = $now->format("Y-m-d H:i:s");
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first(['id'])
            ->id;
        return $this->create($partnerId, $applicationId, $secret, $accessToken, $storeName, $username, $expiredAt);
    }
    private function create($partnerId, $applicationId, $secret, $accessToken, $storeName, $username, $expiredAt)
    {
        try {
            $exists = DB::table('smart_store_accounts')
                ->where('is_active', 'ACTIVE')
                ->Where('application_id', $applicationId)
                ->Where('secret', $secret)
                ->Where('access_token', $accessToken)
                ->exists();
            if ($exists === true) {
                return [
                    'status' => false,
                    'message' => '이미 존재하는 계정입니다.'
                ];
            }
            DB::table('smart_store_accounts')
                ->insert([
                    'partner_id' => $partnerId,
                    'application_id' => $applicationId,
                    'secret' => $secret,
                    'access_token' => $accessToken,
                    'store_name' => $storeName,
                    'username' => $username,
                    'expired_at' => $expiredAt,
                    'hash' => Str::uuid()
                ]);
            return [
                'status' => true,
                'message' => '계정을 성공적으로 연동하였습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터베이스에 추가하는 작업 중 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function getAccessToken($applicationId, $secret, $username)
    {
        $ssac = new SmartStoreApiController();
        $timestamp = (int)round(microtime(true) * 1000);
        $password = $applicationId . '_' . $timestamp;
        $hashed = crypt($password, $secret);
        $clientSecretSign = base64_encode($hashed);
        $params = [
            "client_id" => $applicationId,
            "timestamp" => $timestamp,
            "grant_type" => "client_credentials",
            "client_secret_sign" => $clientSecretSign,
            "type" => "SELF", // "SELLER" 또는 "SELF"
            "account_id" => $username, // 'type'이 'SELLER'일 경우 필요
        ];
        $method = "POST";
        $path = "/v1/oauth2/token";
        $buildResult = $ssac->build($method, $path, $params);
        $data = json_decode($buildResult['data']);
        if ($buildResult['status'] === true) {
            return [
                'status' => true,
                'data' => $data
            ];
        }
        return [
            'status' => false,
            'data' => $data,
            'error' => $buildResult['error']
        ];
    }
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'applicationId' => 'required',
            'secret' => 'required',
            'storeName' => 'required',
            'hash' => 'required'
        ], [
            'username' => '네이버 커머스 판매자 로그인 아이디를 기입해주세요.',
            'applicationId' => '애플리케이션 ID를 입력해주세요.',
            'secret' => '애플리케이션 시크릿 코드를 입력해주세요.',
            'storeName' => '스토어명을 입력해주세요.',
            'hash' => '올바른 계정이 아닙니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $username = trim($request->username);
        $applicationId = trim($request->applicationId);
        $secret = trim($request->secret);
        $storeName = trim($request->storeName);
        $getAccessTokenResult = $this->getAccessToken($applicationId, $secret, $username);
        if ($getAccessTokenResult['status'] === false) {
            return [
                'status' => false,
                'message' => '유효한 API 계정 정보가 아닙니다.'
            ];
        }
        $getAccessTokenData = $getAccessTokenResult['data'];
        $accessToken = $getAccessTokenData->access_token;
        $expiresIn = $getAccessTokenData->expires_in;
        $now = new DateTime();
        $secondsToAdd = new DateInterval('PT' . $expiresIn . 'S');
        $now->add($secondsToAdd);
        $expiredAt = $now->format("Y-m-d H:i:s");
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first(['id'])
            ->id;
        $hash = $request->hash;
        return $this->update($applicationId, $secret, $username, $accessToken, $storeName, $expiredAt, $partnerId, $hash);
    }
    private function update($applicationId, $secret, $username, $accessToken, $storeName, $expiredAt, $partnerId, $hash)
    {
        try {
            DB::table('smart_store_accounts')
                ->where('hash', $hash)
                ->update([
                    'partner_id' => $partnerId,
                    'application_id' => $applicationId,
                    'secret' => $secret,
                    'access_token' => $accessToken,
                    'store_name' => $storeName,
                    'username' => $username,
                    'expired_at' => $expiredAt
                ]);
            return [
                'status' => true,
                'message' => '계정을 성공적으로 업데이트하였습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '데이터베이스에 추가하는 작업 중 에러가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function delete(Request $request)
    {
        $hash = $request->hash;
        try {
            DB::table('smart_store_accounts')
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
                'message' => '유효한 접근이 아닙니다.',
                'error' => $e->getMessage()
            ];
        }
    }
}
