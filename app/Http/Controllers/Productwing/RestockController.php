<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Productwing\SoldOutController;
use Illuminate\Support\Facades\DB;

class RestockController extends Controller
{
    public function index(Request $request)
    {
        $productCode = $request->productCode;
        $soldOutController = new SoldOutController();
        $b2Bs = $soldOutController->getActiveB2Bs();
        $userController = new UserController();
        $user = $userController->getUser($request->rememberToken);
        $processController = new ProcessController();
        $success = [];
        $error = [];
        foreach ($b2Bs as $b2B) {
            $account = $processController->getAccount($user->id, $b2B->vendor_id);
            $response = $this->restock($productCode, $b2B->name_eng, $account->username, $account->password, $b2B->name);
            if ($response['status'] == true) {
                $success[] = $response['return'];
            } else {
                $error[] = $response['return'];
            }
        }
        $this->activeProduct($request->productCode);
        return [
            'success' => $success,
            'error' => $error
        ];
    }
    public function restock($productCode, $b2BEngName, $username, $password, $b2BName)
    {
        $scriptPath = public_path('js/restock/' . $b2BEngName . '.js');
        $command = 'node ' . $scriptPath . ' ' . $username . ' ' . $password . ' ' . $productCode;
        exec($command, $output, $resultCode);
        if ($resultCode == 0 && $output[0] === true) {
            return [
                'status' => true,
                'return' => $b2BName
            ];
        }
        return [
            'status' => false,
            'return' => $b2BName
        ];
    }
    protected function activeProduct($productCode)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->update([
                    'isActive' => 'Y'
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
