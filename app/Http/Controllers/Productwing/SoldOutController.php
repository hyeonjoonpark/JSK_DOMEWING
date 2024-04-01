<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductEditor\IndexController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SoldOutController extends Controller
{
    private $userController;
    private $controller;
    private $indexController;
    public function __construct()
    {
        $this->userController = new UserController();
        $this->controller = new Controller();
        $this->indexController = new IndexController();
    }
    /**
     * 비즈니스 로직
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $validator = $this->validator($request);
        if ($validator->fails()) {
            return [
                'status' => false,
                'return' => $validator->errors()->first()
            ];
        }
        $rememberToken = $request->rememberToken;
        $productCodes = $request->productCodes;
        $b2bIds = $request->b2bs;
        $type = $request->type;
        if (!isset($b2bIds)) {
            $b2bIds = [];
        }
        $validateRememberToken = $this->userController->validateRememberToken($rememberToken);
        if ($validateRememberToken === false) {
            return response()->json([
                'status' => false,
                'return' => '세션이 만료되었습니다. 안전한 서비스 이용을 위해 다시 로그인해주세요.'
            ]);
        }
        $html = $this->runSoldOut($productCodes, $b2bIds, $rememberToken, $type);
        $isSellwingChecked = $request->isSellwingChecked;
        if ($isSellwingChecked === 'true') {
            $inactiveProducts = $this->indexController->inactiveProducts($productCodes, $type);
            $status = $inactiveProducts['status'];
            if ($status === false) {
                return $inactiveProducts;
            }
        }
        return response()->json([
            'status' => true,
            'return' => $html
        ]);
    }
    /**
     * @param array $productCodes
     * @param array $b2bIds
     * @param string $rememberToken
     * @return string
     */
    public function runSoldOut($productCodes, $b2bIds, $rememberToken, $type)
    {
        $tempDirPath = storage_path('app/public/product-codes');
        $tempFilePath = $tempDirPath . '/' . uniqid() . '.json';
        if (!file_exists($tempDirPath)) {
            mkdir($tempDirPath, 0777, true);
        }
        $productCodesChunks = array_chunk($productCodes, 100);
        $errorHtml = '요청에 실패한 업체 및 상품 코드<br><br>';
        foreach ($b2bIds as $b2bId) {
            $account = $this->controller->getVendorAccount($rememberToken, $b2bId);
            $vendor = DB::table('vendors')
                ->where('id', $b2bId)
                ->first(['name_eng', 'name']);
            $vendorEngName = $vendor->name_eng;
            $vendorName = $vendor->name;
            $username = $account->username;
            $password = $account->password;
            foreach ($productCodesChunks as $chunk) {
                file_put_contents($tempFilePath, json_encode($chunk));
                $soldOutResult = $this->sendSoldOutRequest($tempFilePath, $vendorEngName, $username, $password, $type);
                if ($soldOutResult === false) {
                    foreach ($chunk as $productCode) {
                        $errorHtml .= $vendorName . ': ' . $productCode . ' / ';
                    }
                }
                unlink($tempFilePath);
            }
        }
        return $errorHtml;
    }
    /**
     * 스크래핑 봇을 보내는 메소드
     * @param string $productCode
     * @param string $vendorEngName
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function sendSoldOutRequest($tempJsonFilePath, $vendorEngName, $username, $password, $type)
    {
        try {
            $scriptPath = public_path('js/' . $type . '/' . $vendorEngName . '.js');
            $command = "node {$scriptPath} {$username} {$password} {$tempJsonFilePath}";
            exec($command, $output, $resultCode);
            if ($resultCode === 0 && $output[0] === 'true') {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    private function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productCodes' => 'required|array',
            'rememberToken' => 'required|string'
        ], [
            'productCodes' => '품절/재입고 처리할 상품을 하나 이상 선택해주세요.',
            'rememberToken' => '세션이 만료되었습니다. 안전한 서비스 이용을 위해 다시 로그인해주세요.',
        ]);
        return $validator;
    }
}
