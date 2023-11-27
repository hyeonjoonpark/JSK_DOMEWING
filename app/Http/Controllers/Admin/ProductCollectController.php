<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductCollectController extends Controller
{
    public function index(Request $request)
    {
        $validator = $this->validation($request);
        // 유효성 검사 실패 시 오류 반환
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first());
        }
        try {
            $userId = DB::table('users')->where('remember_token', $request->remember_token)->select('id')->first()->id;
            DB::table('collected_products')->insert([
                'userId' => $userId,
                'productName' => $request->productName,
                'productDetail' => $request->productDetail,
                'categoryId' => $request->categoryId,
                'keywords' => $request->keywords,
                'taxability' => $request->taxability,
                'saleToMinor' => $request->saleToMinor,
                'origin' => $request->origin,
                'isMedicalDevice' => $request->isMedicalDevice,
                'isMedicalFoods' => $request->isMedicalFoods,
                'shippingPolicy' => $request->shippingPolicy,
                'productPrice' => $request->productPrice,
                'productVendor' => $request->productVendor,
                'shippingCost' => $request->shippingCost,
                'productImage' => $request->productImage,
                'productInformationId' => $request->productInformationId,
                'productHref' => $request->productHref
            ]);
            return $this->getResponseData(1, '상품을 성공적으로 가공한 후, 수집하였습니다!');
        } catch (Exception $e) {
            return $this->getResponseData(-1, $e->getMessage());
        }
    }
    public function bulk(Request $request)
    {
        $validator = $this->bulkValidation($request);
        if ($validator->fails()) {
            return $this->getResponseData(-1, $validator->errors()->first());
        } else {
            $products = $request->products;
            $categoryId = $request->categoryId;
            $keywords = $request->keywords;
            $userId = DB::table('users')->where('remember_token', $request->remember_token)->select('id')->first()->id;
            try {
                foreach ($products as $product) {
                    $price = $product['price'];
                    // $price = $product->price;
                    DB::table('collected_products')->insert([
                        'userId' => $userId,
                        'productName' => $product['name'],
                        'productDetail' => $product['detail'],
                        'categoryId' => $categoryId,
                        'keywords' => $keywords,
                        'taxability' => 0,
                        'saleToMinor' => 0,
                        'origin' => 2,
                        'isMedicalDevice' => 0,
                        'isMedicalFoods' => 0,
                        'shippingPolicy' => 0,
                        'productPrice' => $price,
                        'productVendor' => "LADAM",
                        'shippingCost' => "3000",
                        'productImage' => $product['image'],
                        'productInformationId' => 43,
                        'productHref' => $product['href']
                    ]);
                }
                return $this->getResponseData(1, '상품을 성공적으로 가공한 후, 수집하였습니다!');
            } catch (Exception $e) {
                return $this->getResponseData(-1, $e->getMessage());
            }
        }
    }
    public function bulkValidation(Request $request)
    {
        Validator::extend('valid_keywords', function ($attribute, $value, $parameters, $validator) {
            // 키워드를 쉼표로 분리
            $keywords = explode(',', $value);

            // 키워드 개수 검사 (5개 이상, 10개 이하)
            $keywordsCount = count($keywords);
            if ($keywordsCount < 5 || $keywordsCount > 10) {
                return false;
            }

            // 각 키워드의 길이 검사 (2글자 이상, 10글자 이하)
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword); // 공백 제거
                if (Str::length($keyword) < 2 || Str::length($keyword) > 10) {
                    return false;
                }
            }

            return true;
        });
        $validator = Validator::make($request->all(), [
            'keywords' => ['required', 'string', 'valid_keywords']
        ], [
            'keywords' => '상품 키워드를 , 단위로 구분하여 최소 5개를 기입해주세요.'
        ]);
        return $validator;
    }
    protected function validation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'remember_token' => 'required|string',
            'productDetail' => 'required|string',
            'productName' => 'required|string',
            'categoryId' => 'required|integer',
            'keywords' => 'required|string|min:9|max:255|unique_keywords',
            'taxability' => 'required|integer',
            'productImage' => 'required|url',
            'saleToMinor' => 'required|integer',
            'origin' => 'required|integer',
            'isMedicalDevice' => 'required|integer',
            'isMedicalFoods' => 'required|integer',
            'shippingPolicy' => 'required|integer',
            'shippingCost' => 'required|integer',
            'productPrice' => 'required|integer',
            'productInformationId' => 'required|integer',
            'productVendor' => 'required|string|min:2|max:255'
        ], [
            'remember_token' => '잘못된 접근입니다.',
            'productDetail' => '상품 상세정보를 작성해주세요.',
            'productName' => '상품명을 기입해주세요.',
            'categoryId' => '상품 카테고리를 설정해주세요.',
            'keywords' => '상품 키워드를 , 단위로 구분하여 최소 5개를 기입해주세요.',
            'taxability' => '과세 여부를 선택해주세요.',
            'productImage' => '상품 이미지를 기입해주세요.',
            'saleToMinor' => '미성년자 판매여부를 선택해주세요.',
            'origin' => '상품 원산지를 기입해주세요.',
            'isMedicalDevice' => '의료기기 여부를 선택해주세요.',
            'isMedicalFoods' => '건강기능식품 여부를 선택해주세요.',
            'shippingPolicy' => '배송 정책을 선택해주세요.',
            'shippingCost' => '배송 금액을 기입해주세요.',
            'productPrice' => '상품 가격을 기입해주세요.',
            'productInformationId' => '상품정보고시를 선택해주세요.',
            'productVendor' => '상품 제조사/브랜드를 2글자 이상으로 기입해주세요.'
        ]);
        return $validator;
    }
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
