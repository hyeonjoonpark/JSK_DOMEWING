<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class SoldOutController extends Controller
{
    protected $processController;
    protected $userController;

    public function __construct(ProcessController $processController, UserController $userController)
    {
        $this->processController = $processController;
        $this->userController = $userController;
    }

    public function index(Request $request)
    {
        $user = $this->userController->getUser($request->rememberToken);
        $b2Bs = $this->getActiveB2Bs();

        $responses = array_map(function ($b2B) use ($user, $request) {
            return $this->processSoldOut($b2B, $user->id, $request->productCode);
        }, $b2Bs->toArray());

        $this->inactiveProduct($request->productCode);

        return [
            'status' => true,
            'return' => $this->formatResponses($responses)
        ];
    }

    protected function inactiveProduct($productCode)
    {
        try {
            DB::table('minewing_products')
                ->where('productCode', $productCode)
                ->update([
                    'isActive' => 'N'
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function processSoldOut($b2B, $userID, $productCode)
    {
        $account = $this->processController->getAccount($userID, $b2B->vendor_id);
        $response = $this->soldOut($productCode, $b2B->name_eng, $account->username, $account->password, $b2B->name);

        return $response;
    }

    protected function soldOut($productCode, $b2BEngName, $username, $password, $b2BName)
    {
        $process = new Process(['node', $this->getScriptPath($b2BEngName), $username, $password, $productCode]);
        $process->run();

        return [
            'status' => $process->isSuccessful(),
            'return' => $b2BName
        ];
    }

    protected function getScriptPath($b2BEngName)
    {
        return public_path("js/sold-out/" . $b2BEngName . ".js");
    }

    protected function getActiveB2Bs()
    {
        return DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->get();
    }

    protected function formatResponses($responses)
    {
        $success = array_filter($responses, function ($res) {
            return $res['status'] === true;
        });

        $error = array_filter($responses, function ($res) {
            return $res['status'] === false;
        });

        return ['success' => array_values($success), 'error' => array_values($error)];
    }
}
