<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Admin\FormController;

class ProductRegisterController extends Controller
{
    // 카테고리 검색
    protected function categorySearch(Request $request)
    {
        $keyword = $request->keyword;

        // 검증 규칙 설정
        $validator = Validator::make($request->all(), [
            'keyword' => ['required', 'min:2', 'max:20']
        ], [
            'keyword' => '검색어는 2자 이상 20자 이하로 기입해주세요.'
        ]);

        // 유효성 검사 실패 시 오류 반환
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first());
        }

        $categories = DB::table('category')->where('wholeCategoryName', 'LIKE', '%' . $keyword . '%')->get();
        if (!empty($categories)) {
            return $this->getResponseData(1, $categories);
        } else {
            return $this->getResponseData(-1, '검색 결과가 없습니다. 다른 키워드로 검색해주세요.');
        }
    }

    // 상품 등록 처리
    protected function handle(Request $request)
    {
        $validator = $this->validation($request);

        // 유효성 검사 실패 시 오류 반환
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first());
        }

        // 상품 설명 이미지 업로드
        $descImage = $this->moveImage($request->remember_token, $request->productDescImage, public_path('assets/images/product/desc/'), 0);
        if ($descImage['status'] == -1) {
            return '상품 설명 이미지를 업로드하는 데 실패했습니다';
        }
        $descImage = $descImage['return'];

        // 상품 대표 이미지 업로드
        $productImage = $this->moveImage($request->remember_token, $request->productImage, public_path('assets/images/product/'), 1);
        if ($productImage['status'] == -1) {
            return '상품 대표 이미지를 업로드하는 데 실패했습니다';
        }
        $productImage = $productImage['return'];

        // 업체 등록 및 실패한 업체 확인
        $failedVendors = [];
        foreach ($request->vendors as $vendorId) {
            $account = $this->getAccount($request, $vendorId);
            $vendor = DB::table('vendors')->where('id', $vendorId)->select('name', 'name_eng')->first();
            $vendorName = $vendor->name;
            $vendorEngName = $vendor->name_eng;
            $data = $this->insertExcel($request, $account->username, $account->password, $vendorEngName, $productImage, $descImage);
            $script = $data['return'];
            if ($data['status'] == -1) {
                $failedVendors[] = $vendorName;
            }
        }

        if (empty($failedVendors)) {
            return $this->getResponseData(1, '등록에 성공했습니다.' . $script);
        } else {
            $failedVendorList = implode(', ', $failedVendors);
            return $this->getResponseData(-1, "등록에 실패한 업체: $script");
        }
    }

    // 상품 등록 처리 (임시)
    protected function operProductRegister(Request $request, $username, $password, $vendorEngName, $imageName, $productDescImage)
    {
        try {
            // Node.js 스크립트 경로
            $scriptPath = public_path('js/register/' . $vendorEngName . '.js');

            // 명령어 문자열 생성
            $command = "node " .
                escapeshellarg($scriptPath) . " " .
                escapeshellarg($request->input('itemName')) . " " .
                escapeshellarg($request->input('invoiceName')) . " " .
                escapeshellarg($request->input('category')) . " " .
                escapeshellarg($request->input('keywords')) . " " .
                escapeshellarg($request->input('taxability')) . " " .
                escapeshellarg($imageName) . " " .
                escapeshellarg($request->input('saleToMinor')) . " " .
                escapeshellarg($request->input('origin')) . " " .
                escapeshellarg($request->input('madicalEquipment')) . " " .
                escapeshellarg($request->input('healthFunctional')) . " " .
                escapeshellarg($request->input('shipping')) . " " .
                escapeshellarg($request->input('price')) . " " .
                escapeshellarg($request->input('vendor')) . " " .
                escapeshellarg($request->input('shipCost')) . " " .
                escapeshellarg($request->input('product_information')) . " " .
                escapeshellarg($request->input('model')) . " " .
                escapeshellarg($productDescImage) . " " .
                escapeshellarg($username) . " " .
                $password;

            // 명령어 실행
            exec($command, $output, $returnCode);

            // 실행 결과에 따라 응답 반환
            if ($returnCode === 0) {
                return $this->getResponseData(1, implode("\n", $output));
            } else {
                return $this->getResponseData(-1, implode("\n", $output));
            }
        } catch (Exception $e) {
            // 예외 발생 시 오류 반환
            return $this->getResponseData(-1, $e->getMessage());
        }
    }

    // 유효성 검사 규칙 설정
    protected function validation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendors' => 'required|array',
            'model' => 'required|string',
            'productDescImage' => 'required|image',
            'itemName' => 'required|string',
            'invoiceName' => 'required|string',
            'category' => 'required',
            'keywords' => 'required|string|min:9|max:255|unique_keywords',
            'taxability' => 'required|string',
            'productImage' => 'required|image',
            'saleToMinor' => 'required|string',
            'origin' => 'required|string',
            'madicalEquipment' => 'required|string',
            'healthFunctional' => 'required|string',
            'shipping' => 'required|string',
            'shipCost' => 'required|numeric',
            'price' => 'required|numeric',
            'vendor' => 'required|string',
            'product_information' => 'required|string',
        ], [
            'vendors' => '상품을 등록할 업체를 선택해주세요',
            'model' => '모델명을 기입해주세요',
            'productDescImage' => '상품 설명을 기입해주세요',
            'category' => '상품 카테고리를 검색 후 선택해주세요',
            'itemName' => '상품명을 기입해주세요',
            'invoiceName' => '택배송장명을 기입해주세요',
            'keywords.unique_keywords' => '키워드가 중복되서는 안 됩니다',
            'keywords' => '키워드를 기입해주세요',
            'productImage' => '상품 대표 이미지를 업로드해주세요',
            'shipCost' => '배송비를 기입해주세요',
            'vendor' => '제조사/브랜드를 기입해주세요',
            'price' => '가격을 기입해주세요'
        ]);

        return $validator;
    }

    // 이미지 업로드 처리
    protected function moveImage($remember_token, $image, $path, $type)
    {
        try {
            $user = DB::table('users')->where('remember_token', $remember_token)->first();
            $userId = $user->id;
            $ext = $image->getClientOriginalExtension();
            $imageName = $userId . "_" . date('YmdHis') . "." . $ext;

            if ($type == 1) {
                $image = Image::make($image)->fit(1000, 1000);
                $image->save($path . $imageName);
            } else {
                $image->move($path, $imageName);
            }

            return $this->getResponseData(1, $imageName);
        } catch (Exception $e) {
            return $this->getResponseData(-1, $e->getMessage());
        }
    }

    // 업체 계정 가져오기
    protected function getAccount(Request $request, $vendorId)
    {
        $account = DB::table('accounts')
            ->join('users', 'users.id', '=', 'accounts.user_id')
            ->join('vendors', 'vendors.id', '=', 'accounts.vendor_id')
            ->where('users.remember_token', $request->remember_token)
            ->where('vendors.id', $vendorId)
            ->where('accounts.is_active', 'Y')
            ->select('accounts.username', 'accounts.password')
            ->first();

        return $account;
    }

    public function insertExcel(Request $request, $username, $password, $vendorEngName, $productImage, $descImage)
    {
        $data = new FormController();
        $data = $data->$vendorEngName($request, $username, $password, $request->category, $productImage, $descImage);

        return $data;
    }

    // 응답 데이터 생성
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}