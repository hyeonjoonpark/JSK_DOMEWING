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
        $keyword = $request->keyword; // 요청에서 키워드 값을 가져옴

        // 검증 규칙 설정
        $validator = Validator::make($request->all(), [
            'keyword' => ['required', 'min:2', 'max:20'] // 키워드 필드에 대한 유효성 검사 규칙 설정
        ], [
            'keyword' => '검색어는 2자 이상 20자 이하로 기입해주세요.' // 유효성 검사 실패 시 반환할 오류 메시지 설정
        ]);

        // 유효성 검사 실패 시 오류 반환
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first()); // 유효성 검사 실패 시 오류 메시지 반환
        }

        $categories = DB::table('ownerclan_category')->where('name', 'LIKE', '%' . $keyword . '%')->get(); // 카테고리 테이블에서 키워드를 포함하는 카테고리 검색
        if (!empty($categories)) {
            return $this->getResponseData(1, $categories); // 검색 결과가 있을 경우 결과 반환
        } else {
            return $this->getResponseData(-1, '검색 결과가 없습니다. 다른 키워드로 검색해주세요.'); // 검색 결과가 없을 경우 오류 메시지 반환
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
        // $descImage = $this->moveImage($request->remember_token, $request->productDescImage, public_path('assets/images/product/desc/'), 0);
        // if ($descImage['status'] == -1) {
        //     return '상품 설명 이미지를 업로드하는 데 실패했습니다';
        // }
        $descImage = $request->productDesc;

        // 상품 대표 이미지 업로드
        $productImage = $this->moveImage($request->remember_token, $request->productImage, public_path('assets/images/product/'), 1);
        if ($productImage['status'] == -1) {
            return '상품 대표 이미지를 업로드하는 데 실패했습니다';
        }
        $productImage = $productImage['return'];

        // 업체 등록 및 실패한 업체 확인
        $failedVendors = [];
        $cnt = 0;
        foreach ($request->vendors as $vendorId) {
            $account = $this->getAccount($request, $vendorId);
            $vendor = DB::table('vendors')->where('id', $vendorId)->select('name', 'name_eng')->first();
            $vendorName = $vendor->name;
            $vendorEngName = $vendor->name_eng;
            $data = $this->insertExcel($request, $account->username, $account->password, $vendorEngName, $productImage, $descImage);
            if ($data['status'] == -1) {
                $failedVendors[] = $vendorName . " " . $data['return'];
            } else {
                // $formedExcel = $data['return'];
                // set_time_limit(0);
                // $command = 'node ' . public_path('js/register/' . $vendorEngName . '.js') . " \"$account->username\" \"$account->password\" \"$formedExcel\"";
                // try {
                //     exec($command, $output, $exitCode);
                //     if ($exitCode !== 0) {
                //         // 명령이 성공적으로 실행됨
                //         $failedVendors[] = $vendorName;
                //     }
                // } catch (Exception $e) {
                //     $failedVendors[] = $vendorName;
                // }
                if ($cnt < 3) {
                    $formedExcel = $data['return'];
                    set_time_limit(0);
                    $command = 'node ' . public_path('js/register/' . $vendorEngName . '.js') . " \"$account->username\" \"$account->password\" \"$formedExcel\"";
                    try {
                        exec($command, $output, $exitCode);
                        if ($exitCode !== 0) {
                            // 명령이 성공적으로 실행됨
                            $failedVendors[] = $vendorName;
                        }
                    } catch (Exception $e) {
                        $failedVendors[] = $vendorName;
                    }
                }
            }
            $cnt++;
        }

        if (empty($failedVendors)) {
            return $this->getResponseData(1, '등록에 성공했습니다.');
        } else {
            $failedVendorList = implode(', ', $failedVendors);
            return $this->getResponseData(-1, "등록에 실패한 업체: $failedVendorList");
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
            'productDesc' => 'required',
            'itemName' => 'required|string|min:2|max:20',
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
            'productDesc' => '상품 설명을 기입해주세요',
            'category' => '상품 카테고리를 검색 후 선택해주세요',
            'itemName' => '상품명을 기입해주세요. 2자 이상 20자 이하.',
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
        // 사용자 정보 가져오기
        $user = DB::table('users')->where('remember_token', $remember_token)->first();
        $userId = $user->id;

        // 이미지 파일 이름 생성
        $ext = $image->getClientOriginalExtension();
        $imageName = $userId . "_" . date('YmdHis') . "." . $ext;

        // 이미지 리사이징 및 저장 (Type 1: 리사이즈, Type 2: 그대로 저장)
        if ($type == 1) {
            $image = Image::make($image)->fit(1000, 1000);
            $image->save($path . $imageName);
        } else {
            $image->move($path, $imageName);
        }

        // 이미지 데이터 가져오기
        $imageData = file_get_contents($path . $imageName);

        // Imgur API에 이미지 업로드
        $clientID = '52d53ac9b9f957b';
        $response = $this->uploadToImgur($imageData, $clientID);

        // 응답 처리
        if ($response['success'] === true && isset($response['data']['link'])) {
            $imageLink = $response['data']['link'];
            return $this->getResponseData(1, $imageLink);
        } else {
            return $this->getResponseData(-1, $response['success']);
        }
    }

    function uploadToImgur($imageData, $clientID)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                'Authorization: Client-ID ' . $clientID
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            array(
                'image' => base64_encode($imageData),
            )
        );
        // 응답 받기
        $response = curl_exec($curl);
        curl_close($curl);

        // JSON 응답 디코드
        return json_decode($response, true);
    }

    // 업체 계정 가져오기
    public function getAccount(Request $request, $vendorId)
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
