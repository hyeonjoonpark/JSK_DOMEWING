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
