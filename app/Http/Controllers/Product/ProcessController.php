<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

use function PHPUnit\Framework\isEmpty;

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
        $vendor = $this->getVendor($vendorID);
        $user = $this->getUser($rememberToken);
        $account = $this->getAccount($user->id, $vendor->id);
        if (!$vendor || !$user || !$account) {
            return [
                'status' => false,
                'return' => '세션이 만료되었습니다. 다시 로그인해 주십시오.'
            ];
        }
        $products = [];
        foreach ($productHrefs as $productHref) {
            $products[] = $this->scrapeProductDetails($vendor->name_eng, $account->username, $account->password, $productHref);
        }
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
    protected function getVendor($sellerID)
    {
        $seller = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('id', $sellerID)
            ->first();
        return $seller;
    }
    public function scrapeProductDetails($sellerEngName, $username, $password, $productHref)
    {
        $scriptPath = public_path('js/details/' . $sellerEngName . '.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($productHref) . " " . escapeshellarg($username) . " " . $password;
        exec($command, $output, $returnCode);
        if ($returnCode == 0 && isset($output[0])) {
            $result = json_decode($output[0], true);
            return $result;
        }
        return response()->json(['error' => 'Failed to parse product data'], 500);
    }
    // public function scrapeProductDetails($sellerEngName, $username, $password, $productHref)
    // {
    //     // Node.js 스크립트 경로와 필요한 인자
    //     $scriptPath = public_path('js/details/' . $sellerEngName . '.js');
    //     // Node.js 스크립트 실행
    //     $process = new Process(['node', $scriptPath, $productHref, $username, $password]);
    //     $process->run();
    //     // 스크립트 실행에 실패한 경우 예외 처리
    //     if (!$process->isSuccessful()) {
    //         throw new ProcessFailedException($process);
    //     }
    //     // 출력 결과 추출 및 JSON 파싱
    //     $result = json_decode($process->getOutput(), true);
    //     if ($result === null) {
    //         return response()->json(['error' => 'Failed to parse product data'], 500);
    //     }
    //     // 결과 데이터를 사용하여 필요한 작업 수행
    //     // 예: 데이터베이스에 저장, 추가 처리 등
    //     return $result;
    // }
}
