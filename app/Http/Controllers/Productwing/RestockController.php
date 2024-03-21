<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\DB;

class RestockController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $productCode = $request->productCode;
        $Controller = new Controller();
        $b2Bs = $Controller->getActiveB2Bs();
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
        $status = $resultCode == 0 && isset($output[0]) && $output[0] == 'true';
        return [
            'status' => $status,
            'return' => $b2BName,
        ];
    }
    protected function activeProduct($productCode)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->whereNotIn('v.id', [6, 33, 35])
                ->update([
                    'isActive' => 'Y',
                    'updatedAt' => now()
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
