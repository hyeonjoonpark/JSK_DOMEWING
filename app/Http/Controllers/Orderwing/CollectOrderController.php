<?php

namespace App\Http\Controllers\Orderwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\Productwing\SoldOutController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

class CollectOrderController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $userController = new UserController();
        $soldOutController = new SoldOutController();
        $processController = new ProcessController();
        $extractOrderController = new ExtractOrderController();
        $rememberToken = $request->rememberToken;
        $user = $userController->getUser($rememberToken);
        $userID = $user->id;
        $b2Bs = $soldOutController->getActiveB2Bs();
        $success = [];
        $error = [];
        foreach ($b2Bs as $b2B) {
            $b2BEngName = $b2B->name_eng;
            $b2BVendorID = $b2B->vendor_id;
            $account = $processController->getAccount($userID, $b2BVendorID);
            $username = $account->username;
            $password = $account->password;
            $this->deleteLegacy($b2BEngName);
            $response = $this->getOrderExcelFile($b2BEngName, $b2BVendorID, $username, $password);
            if ($response['status'] === true) {
                $success[] = $response['return'];
            } else {
                $error[] = $response['return'];
            }
        }
        return [
            'status' => true,
            'return' => [
                'success' => $success,
                'error' => $error
            ]
        ];
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
    public function requestExcelFile($b2BEngName = 'domesin', $b2BVendorID = 6, $username = 'sungiltradekorea', $password = 'tjddlf88!@')
    {
        $scriptPath = public_path('js/orderwing/' . $b2BEngName . '_process.js');
        $command = 'node ' . $scriptPath . ' ' . $username . ' ' . $password;
        exec($command, $output, $resultCode);
        if ($output === true) {
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
    public function getOrderExcelFile($b2BEngName, $b2BVendorID, $username, $password)
    {
        $scriptPath = public_path('js/orderwing/' . $b2BEngName . '.js');
        $command = 'node ' . $scriptPath . ' ' . $username . ' ' . $password;
        exec($command, $output, $resultCode);
        if ($output === true) {
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
}
