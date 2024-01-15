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
    public function index(Request $request)
    {
        set_time_limit(0);
        $userController = new UserController();
        $user = $userController->getUser($request->rememberToken);
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
                    'isActive' => 'N',
                    'updatedAt' => now()
                ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function processSoldOut($b2B, $userID, $productCode)
    {
        $processController = new ProcessController();
        $account = $processController->getAccount($userID, $b2B->vendor_id);
        $response = $this->soldOut($productCode, $b2B->name_eng, $account->username, $account->password, $b2B->name);

        return $response;
    }

    public function soldOut($productCode, $b2BEngName, $username, $password, $b2BName)
    {
        $scriptPath = $this->getScriptPath($b2BEngName);
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($username) . " " . $password . " " . escapeshellarg($productCode);

        // Execute the command
        exec($command, $output, $resultCode);

        // Check the result code and output
        $status = $resultCode == 0 && isset($output[0]) && $output[0] == 'true';

        return [
            'status' => $status,
            'return' => $b2BName,
        ];
    }

    protected function getScriptPath($b2BEngName)
    {
        return public_path("js/sold-out/" . $b2BEngName . ".js");
    }

    public function getActiveB2Bs()
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
