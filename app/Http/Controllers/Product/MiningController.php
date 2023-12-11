<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MiningController extends Controller
{
    public function index(Request $request)
    {
        $remember_token = $request->remember_token;
        $sellerID = $request->sellerID;
        $seller = $this->getVendor($sellerID);
        $listURL = $request->listURL;
        $account = $this->getAccount($remember_token, $sellerID);
        if (!$account) {
            return [
                'status' => false,
                'return' => '잘못된 접근입니다.'
            ];
        }
        $response = $this->getProductsList($seller, $listURL, $account);
        return $response;
    }
    public function getVendor($vendorID)
    {
        $vendor = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('ps.is_active', 'Y')
            ->where('ps.vendor_id', $vendorID)
            ->where('v.is_active', 'ACTIVE')
            ->first();
        return $vendor;
    }
    protected function getAccount($remember_token, $sellerID)
    {
        $account = DB::table('accounts AS a')
            ->join('users AS u', 'a.user_id', '=', 'u.id')
            ->where('u.remember_token', $remember_token)
            ->where('u.is_active', 'ACTIVE')
            ->where('a.vendor_id', $sellerID)
            ->select('a.username', 'a.password')
            ->first();
        return $account;
    }
    public function getProductsList($seller, $listURL, $account)
    {
        $scriptPath = public_path('js/mining/' . $seller->name_eng . '.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($listURL) . " " . escapeshellarg($account->username) . " " . $account->password;
        try {
            set_time_limit(0);
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $products = json_decode($output[0], true);
                return [
                    'status' => true,
                    'return' => $products
                ];
            } else {
                return [
                    'status' => false,
                    'return' => '상품 데이터 추출에 실패했습니다.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
