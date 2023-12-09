<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductSearchController extends Controller
{
    public function index(Request $request)
    {
        $validateSearch = $this->validateSearch($request);
        if (!$validateSearch['status']) {
            return $validateSearch;
        }
        $userController = new UserController();
        $vendorController = new VendorController();
        $userID = $userController->getUser($request->remember_token)->id;
        $keyword = $request->searchKeyword;
        $products = [];
        foreach ($request->searchVendors as $vendorID) {
            $account = $this->getAccount($vendorID, $userID);
            $username = $account->username;
            $password = $account->password;
            $vendor = $vendorController->getVendor($vendorID);
            if ($this->runSearchScript($username, $password, $vendor, $keyword)) {
            }
            $products[] = $this->runSearchScript($username, $password, $vendor, $keyword);
        }
        return [
            'status' => true,
            'return' => $products,
            'message' => '총 ' . count($products) . '건의 상품이 검색되었습니다.'
        ];
    }

    public function validateSearch(Request $request)
    {
        if (mb_strlen($request->searchKeyword, 'UTF-8') < 2 || mb_strlen($request->searchKeyword, 'UTF-8') > 10) {
            return [
                'status' => false,
                'message' => '검색 키워드는 2글자 이상, 10글자 이하입니다.'
            ];
        }
        if (count($request->searchVendors) < 1) {
            return [
                'status' => false,
                'message' => '검색 엔진에 활용할 업체를 한 군데 이상 선택해주세요.'
            ];
        }
        return [
            'status' => true
        ];
    }

    public function getAccount($vendorID, $userID)
    {
        try {
            $account = DB::table('accounts')
                ->where('user_id', $userID)
                ->where('vendor_id', $vendorID)
                ->first(['username', 'password']);
            return $account;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function runSearchScript($username, $password, $vendor, $keyword)
    {
        $nodeScriptPath = public_path('js/search/' . $vendor->name_eng . '.js');
        $command = "node " . escapeshellarg($nodeScriptPath) . " " . escapeshellarg($username) . " " . escapeshellarg($password) . " " . escapeshellarg($keyword);
        try {
            exec($command, $output, $returnCode);
            if ($returnCode === 0) {
                $jsonString = $output[0];
                $products = json_decode($jsonString, true);
                return [
                    'status' => true,
                    'return' => $products
                ];
            }
            return [
                'status' => false,
                'return' => $vendor->name
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'return' => $vendor->name,
                'message' => $e->getMessage()
            ];
        }
    }
}
