<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class ProcessController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $productHrefs = $request->productHrefs;
        $sellerID = $request->sellerID;
        $remember_token = $request->remember_token;
        $seller = $this->getSeller($sellerID);
        $user = $this->getUser($remember_token);
        $account = $this->getAccount($user->id, $seller->id);
        if (!$seller || !$user || !$account) {
            return [
                'status' => false,
                'message' => '잘못된 접근입니다.'
            ];
        }
        $productDetails = [];
        foreach ($productHrefs as $productHref) {
            $productDetails[] = $this->scrapeProductDetails($seller->name_eng, $account->username, $account->password, $productHref);
        }
        return [
            'status' => true,
            'return' => $productDetails,
            'message' => '"상품 정보들을 무사히 가져왔어요.<br>상품을 가공 중이에요."'
        ];
    }
    protected function getAccount($userID, $sellerID)
    {
        $account = DB::table('accounts')
            ->where('user_id', $userID)
            ->where('vendor_id', $sellerID)
            ->first();
        return $account;
    }
    protected function getUser($remember_token)
    {
        $user = DB::table('users')
            ->where('remember_token', $remember_token)
            ->where('is_active', 'ACTIVE')
            ->first();
        return $user;
    }
    protected function getSeller($sellerID)
    {
        $seller = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('id', $sellerID)
            ->first();
        return $seller;
    }
    public function scrapeProductDetails($sellerEngName, $username, $password, $productHref)
    {
        // Node.js 스크립트 경로와 필요한 인자
        $scriptPath = public_path('js/details/' . $sellerEngName . '.js');
        // Node.js 스크립트 실행
        $process = new Process(['node', $scriptPath, $productHref, $username, $password]);
        $process->run();
        // 스크립트 실행에 실패한 경우 예외 처리
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // 출력 결과 추출 및 JSON 파싱
        $result = json_decode($process->getOutput(), true);
        if ($result === null) {
            return response()->json(['error' => 'Failed to parse product data'], 500);
        }
        // 결과 데이터를 사용하여 필요한 작업 수행
        // 예: 데이터베이스에 저장, 추가 처리 등
        return $result;
    }
}
