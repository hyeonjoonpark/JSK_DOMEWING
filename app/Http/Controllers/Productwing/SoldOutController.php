<?php

namespace App\Http\Controllers\Productwing;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductEditor\IndexController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $validateRememberToken = $this->userController->validateRememberToken($rememberToken);
        if ($validateRememberToken === false) {
            return response()->json([
                'status' => false,
                'return' => '세션이 만료되었습니다. 안전한 서비스 이용을 위해 다시 로그인해주세요.'
            ]);
        }
        $html = $this->runSoldOut($productCodes, $b2bIds, $rememberToken);
        $isSellwingChecked = $request->isSellwingChecked;
        if ($isSellwingChecked === true) {
            $inactiveProducts = $this->indexController->inactiveProducts($productCodes);
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
    protected function runSoldOut($productCodes, $b2bIds, $rememberToken)
    {
        $html = '상품 코드 및 실패한 업체 리스트<br><br><br>';
        foreach ($productCodes as $productCode) {
            $loopB2bIds = $this->loopB2bIds($productCode, $b2bIds, $rememberToken);
            $html .= $productCode . ': ' . $loopB2bIds . '<br><br>';
        }
        return $html;
    }
    /**
     * 선택된 업체들을 위한 반복문
     * @param string $productCode
     * @param array $b2bIds
     * @param string $rememberToken
     * @return string
     */
    protected function loopB2bIds($productCode, $b2bIds, $rememberToken)
    {
        $errors = '';
        foreach ($b2bIds as $b2bId) {
            $getVendor = $this->controller->getVendor($b2bId);
            $status = $getVendor['status'];
            if ($status === false) {
                return $getVendor;
            }
            $vendor = $getVendor['return'];
            $vendorEngName = $vendor->name_eng;
            $vendorName = $vendor->name;
            $account = $this->controller->getVendorAccount($rememberToken, $b2bId);
            $username = $account->username;
            $password = $account->password;
            $sendSoldOutRequest = $this->sendSoldOutRequest($productCode, $vendorEngName, $username, $password);
            if ($sendSoldOutRequest === false) {
                if ($errors === '') {
                    $errors .= $vendorName;
                } else {
                    $errors .= ', ' . $vendorName;
                }
            }
        }
        return $errors;
    }
    /**
     * 스크래핑 봇을 보내는 메소드
     * @param string $productCode
     * @param string $vendorEngName
     * @param string $username
     * @param string $password
     * @return boolean
     */
    protected function sendSoldOutRequest($productCode, $vendorEngName, $username, $password)
    {
        $script = public_path('js/sold-out/' . $vendorEngName . '.js');
        $command = 'node ' . $script . ' ' . $username . ' ' . $password . ' ' . $productCode;
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && $output[0] === 'true') {
            return true;
        }
        return false;
    }
    protected function validator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productCodes' => 'required|array',
            'rememberToken' => 'required|string',
            'b2bs' => 'required|array'
        ], [
            'productCodes' => '품절 처리할 상품을 하나 이상 선택해주세요.',
            'rememberToken' => '세션이 만료되었습니다. 안전한 서비스 이용을 위해 다시 로그인해주세요.',
            'b2bs' => '품절 요청을 보낼 업체를 하나 이상 선택해주세요.'
        ]);
        return $validator;
    }
}
