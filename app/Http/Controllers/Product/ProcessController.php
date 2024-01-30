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
        $errors = [];
        foreach ($productHrefs as $productHref) {
            $response = $this->scrapeProductDetails($vendor->name_eng, $account->username, $account->password, $productHref);
            if ($response['status'] == true) {
                $products[] = $response['return'];
            } else {
                $errors[] = [
                    'product' => $productHref,
                    'message' => $response['return'],
                ];
            }
        }
        return [
            'status' => true,
            'return' => [
                'products' => $products,
                'errors' => $errors,
            ],
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
    public function scrapeProductDetails($sellerEngName, $username, $password, $productHref)
    {
        $scriptPath = public_path('js/details/' . $sellerEngName . '.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($productHref) . " " . escapeshellarg($username) . " " . $password;
        exec($command, $output, $returnCode);
        if ($returnCode == 0 && isset($output[0])) {
            $result = json_decode($output[0], true);
            if ($result == 'false' || $result == false) {
                return [
                    'status' => false,
                    'return' => '재고가 5개 미만인 상품입니다. ' . $result,
                ];
            }
            return [
                'status' => true,
                'return' => $result,
            ];
        }
        return [
            'status' => false,
            'return' => '아래 상품 정보를 추출하는 과정에서 오류가 발생했습니다. ' . $productHref,
        ];
    }
}
