<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoldOutController extends Controller
{
    public function index(Request $request)
    {
        $validator = $request->validate([
            'rememberToken' => 'required',
            'b2bs' => 'required|array',
            'productCodes' => 'required|array',
        ], [
            'rememberToken' => '로그인 세션이 만료되었습니다. 다시 로그인해주십시오.',
            'b2bs' => '품절 요청을 보낼 업체들을 선택해주세요.',
            'productCodes' => '품절 요청을 보낼 상품들을 선택해주세요.'
        ]);
        $userController = new UserController();
        $user = $userController->getUser($request->rememberToken);
        $b2bIds = $request->b2bs;
        $productCodes = $request->productCodes;
        if (!is_array($productCodes)) {
            return [
                'status' => false,
                'return' => '"품절 요청을 보낼 상품들을 선택해주세요."'
            ];
        }
        if (!is_array($b2bIds)) {
            return [
                'status' => false,
                'return' => '"품절 요청을 보낼 업체들을 선택해주세요."'
            ];
        }
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->whereIn('v.id', $b2bIds)
            ->get();
        $returnHtml = '품절 요청에 실패한 리스트<br>';
        foreach ($productCodes as $productCode) {
            foreach ($b2bs as $b2b) {
                $returnHtml .= $b2b->name . ': ';
                $response = $this->processSoldOut($b2b, $user->id, $productCode);
                if ($response['status'] !== true) {
                    $returnHtml .= $response['return'] . ', ';
                }
            }
            $returnHtml .= '<br>';
            $this->inactiveProduct($productCode);
        }

        return [
            'status' => true,
            'return' => $returnHtml
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
