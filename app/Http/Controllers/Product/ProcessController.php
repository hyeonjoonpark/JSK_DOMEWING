<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $productHrefs = $request->productHrefs;
        if (!isset($productHrefs)) {
            return [
                'status' => false,
                'return' => '이미 등록된 상품셋입니다.'
            ];
        }
        $vendorID = $request->vendorID;
        $rememberToken = $request->rememberToken;
        if (empty($productHrefs)) {
            return response()->json([
                'status' => false,
                'return' => '가공 및 저장할 상품을 선택해주세요.'
            ]);
        }
        $vendor = $this->getVendor($vendorID);
        $user = $this->getUser($rememberToken);
        $account = $this->getAccount($user->id, $vendor->id);
        if (!$vendor || !$user || !$account) {
            return [
                'status' => false,
                'return' => '세션이 만료되었습니다. 다시 로그인해 주십시오.'
            ];
        }
        $scrapeProductDetails = $this->scrapeProductDetails($productHrefs, $vendor->name_eng, $account->username, $account->password);
        if ($scrapeProductDetails['status'] === false) {
            return $scrapeProductDetails;
        }
        $products = $scrapeProductDetails['return'];
        return [
            'status' => true,
            'return' => $products
        ];
    }
    private function scrapeProductDetails($productHrefs, $sellerEngName, $username, $password)
    {
        // 임시 파일에 URL 배열 저장
        $tempFilePath = storage_path('app/' . uniqid() . '.json');
        file_put_contents($tempFilePath, json_encode($productHrefs));
        // Node.js 스크립트 실행
        $scriptPath = public_path('js/minewing/details/' . $sellerEngName . '.js');
        $command = "node {$scriptPath} {$tempFilePath} {$username} {$password}";
        exec($command, $output, $returnCode);
        // 실행 결과 확인
        if ($returnCode === 0 && isset($output[0])) {
            $products = json_decode($output[0], true);
        } else {
            return [
                'status' => false,
                'return' => '"상품 상세 정보 추출에 실패했습니다."'
            ];
        }
        // 임시 파일 삭제
        unlink($tempFilePath);
        return [
            'status' => true,
            'return' => $products
        ];
    }
    public function getAccount($userID, $sellerID)
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
    public function getVendor($sellerID)
    {
        $seller = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('id', $sellerID)
            ->first();
        return $seller;
    }
    // public function scrapeProductDetails($sellerEngName, $username, $password, $productHref)
    // {
    //     // 스크립트 파일 경로 확인
    //     $scriptPath = public_path('js/details/' . $sellerEngName . '.js');
    //     if (!file_exists($scriptPath)) {
    //         return [
    //             'status' => false,
    //             'return' => '스크립트 파일을 찾을 수 없습니다: ' . $scriptPath,
    //         ];
    //     }
    //     // Node.js 스크립트 실행
    //     $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($productHref) . " " . escapeshellarg($username) . " " . $password;
    //     exec($command, $output, $returnCode);
    //     // 실행 결과 확인
    //     if ($returnCode !== 0 || !isset($output[0])) {
    //         return [
    //             'status' => false,
    //             'return' => '상품 정보 추출 과정에서 오류가 발생했습니다',
    //         ];
    //     }
    //     // 결과 처리
    //     $result = json_decode($output[0], true);
    //     return [
    //         'status' => true,
    //         'return' => $result,
    //     ];
    // }
}
