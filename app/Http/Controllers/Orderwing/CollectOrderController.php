<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectOrderController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $this->requestExcelFile();
        $this->requestExcelFile2();
        $userController = new UserController();
        $processController = new ProcessController();
        $extractOrderController = new ExtractOrderController();
        $rememberToken = $request->rememberToken;
        $user = $userController->getUser($rememberToken);
        $userID = $user->id;
        $b2Bs = $this->getOrderwingB2bs();
        foreach ($b2Bs as $b2B) {
            $b2BEngName = $b2B->name_eng;
            $b2BVendorID = $b2B->vendor_id;
            $account = $processController->getAccount($userID, $b2BVendorID);
            $username = $account->username;
            $password = $account->password;
            $this->deleteLegacy($b2BEngName);
            $this->getOrderExcelFile($b2BEngName, $b2BVendorID, $username, $password);
        }
        return $extractOrderController->index($b2Bs);
    }
    public function getOrderwingB2bs()
    {
        return DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->whereNotIn('v.id', [6])
            ->get();
    }
    public function deleteLegacy($b2BEngName)
    {
        $dir = public_path('assets/excel/orderwing/' . $b2BEngName . '/*'); // 디렉토리 경로 설정
        // glob() 함수로 디렉토리 내 모든 파일 목록을 얻기
        foreach (glob($dir) as $file) {
            // 파일이면 삭제
            if (is_file($file)) {
                unlink($file); // 파일 삭제
            }
        }
    }
    public function requestExcelFile($b2BEngName = 'domeggook', $username = 'sungil2018', $password = "tjddlf88!@")
    {
        $scriptPath = public_path('js/orderwing/' . $b2BEngName . '_process.js');
        $command = 'node ' . $scriptPath . ' ' . $username . ' ' . $password;
        exec($command, $output, $resultCode);
    }
    public function requestExcelFile2($b2BEngName = 'domeggook2', $username = 'luminous2020', $password = "fnalshtm88!@")
    {
        $scriptPath = public_path('js/orderwing/' . $b2BEngName . '_process.js');
        $command = 'node ' . $scriptPath . ' ' . $username . ' ' . $password;
        exec($command, $output, $resultCode);
    }
    public function getOrderExcelFile($b2BEngName, $b2BVendorID, $username, $password)
    {
        $scriptPath = public_path('js/orderwing/' . $b2BEngName . '.js');
        $command = 'node ' . $scriptPath . ' ' . $username . ' ' . $password;
        exec($command, $output, $resultCode);
        if (isset($output[0])) {
            if ($output[0] === 'true') {
                return [
                    'status' => true,
                    'return' => $b2BVendorID
                ];
            }
            return [
                'status' => false,
                'return' => $b2BVendorID
            ];
        }
        return [
            'status' => false,
            'return' => $b2BVendorID
        ];
    }
}
