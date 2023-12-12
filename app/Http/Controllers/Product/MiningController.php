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
        $numPages = $this->getNumPage($listURL, $seller);
        $account = $this->getAccount($remember_token, $sellerID);
        if (!$account) {
            return [
                'status' => false,
                'return' => '잘못된 접근입니다.'
            ];
        }
        $response = $this->getProductsList($seller, $listURL, $account, $numPages);
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
    public function getNumPage($listURL)
    {
        $scriptPath = public_path('js/pagination/metaldiy.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($listURL);
        try {
            set_time_limit(0);
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && isset($output[0])) {
                $numProducts = (int) $output[0];
                $numPages = (int)ceil($numProducts / 60);
                return [
                    'status' => true,
                    'return' => $numPages
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
    public function getProductsList($seller, $listURL, $account, $numPages)
    {
        $scriptPath = public_path('js/mining/' . $seller->name_eng . '.js');

        // Validate input
        if (!file_exists($scriptPath)) {
            return [
                'status' => false,
                'return' => 'Script file not found.'
            ];
        }

        $allProducts = [];
        $numPages = (int)$numPages;
        for ($i = 1; $i <= $numPages; $i++) {
            $command = sprintf(
                'node %s %s %s %s %d',
                escapeshellarg($scriptPath),
                escapeshellarg($listURL),
                escapeshellarg($account->username),
                $account->password,
                escapeshellarg($i)
            );
            try {
                // Consider limiting the execution time to a reasonable amount
                set_time_limit(0); // Example: 60 seconds
                exec($command, $output, $returnCode);
                if ($returnCode === 0 && isset($output[0])) {
                    $products = json_decode($output[0], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $allProducts = array_merge($allProducts, $products);
                    } else {
                        // Handle JSON decoding error
                        return [
                            'status' => false,
                            'return' => 'JSON decoding error.'
                        ];
                    }
                } else {
                    // Handle command execution error
                    return [
                        'status' => false,
                        'return' => "Error executing command, return code: $returnCode."
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'status' => false,
                    'return' => $e->getMessage()
                ];
            }
        }
        return [
            'status' => true,
            'return' => $allProducts
        ];
    }
}
