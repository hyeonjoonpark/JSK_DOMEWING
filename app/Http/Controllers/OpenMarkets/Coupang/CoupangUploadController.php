<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoupangUploadController extends Controller
{
    private $products, $partner, $account;
    public function __construct($products, $partner, $account)
    {
        $this->products = $products;
        $this->partner = $partner;
        $this->account = $account;
    }
    public function main()
    {
        $products = $this->products;
        $account = $this->account;
        $contentType = 'application/json;charset=UTF-8';
        $method = 'POST';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products';
        $ac = new ApiController();
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $success = 0;
        $duplicated = [];
        $error = [];
        $responseOutbound = $this->getOutbound($accessKey, $secretKey);
        $responseReturn = $this->getReturnCenter($accessKey, $secretKey, $account->code);
        if ($responseOutbound['status'] === false) {
            return $responseOutbound;
        }
        if ($responseReturn['status'] === false) {
            return $responseReturn;
        }
        $outboundCode = $responseOutbound['data'];
        $returnCenter = $responseReturn['data'];
        foreach ($products as $product) {
            $exists = DB::table('coupang_uploaded_products')
                ->where('is_active', 'Y')
                ->where('coupang_account_id', $account->id)
                ->where('product_id', $product->id)
                ->exists();
            if ($exists === true) {
                $duplicated[] = $product->productCode;
                continue;
            }
            // 5,000원 미만 상품들은 상품가와 배송비를 따로 구분
            $productPrice = $product->productPrice;
            $shippingFee = $product->shipping_fee;
            $salePrice = $productPrice;
            // 5,000원 이상 상품들은 상품가에 배송비를 더한 후, 배송비를 0원 처리
            if ($productPrice >= 5000) {
                $salePrice = (int)($product->productPrice + $product->shipping_fee);
                $shippingFee = 0;
            }
            $data = $this->generateData($product, $account, $outboundCode, $returnCenter, $salePrice, $shippingFee, $productPrice);
            $uploadResult = $ac->builder($accessKey, $secretKey, $method, $contentType, $path, $data);
            if ($uploadResult['status'] === true && $uploadResult['data']['code'] === 'SUCCESS') {
                $originProductNo = $uploadResult['data']['data'];
                $this->store($account->id, $product->id, $salePrice, $shippingFee, $originProductNo, $product->productName);
                $success++;
            } else {
                $error[] = [
                    'productCode' => $product->productCode,
                    'error' => $uploadResult
                ];
            }
        }
        return [
            'status' => true,
            'message' => "총 " . count($this->products) . " 개의 상품들 중 <strong>$success</strong>개의 상품을 성공적으로 업로드했습니다.<br>" . count($duplicated) . "개의 중복 상품을 필터링했습니다.",
            'error' => $error
        ];
    }
    protected function store($coupangAccountId, $productId, $price, $shippingFee, $originProductNo, $productName)
    {
        DB::table('coupang_uploaded_products')
            ->insert([
                'coupang_account_id' => $coupangAccountId,
                'product_id' => $productId,
                'price' => $price,
                'shipping_fee' => $shippingFee,
                'origin_product_no' => $originProductNo,
                'product_name' => $productName
            ]);
    }
    protected function getCategoryRelatedMeta($accessKey, $secretKey, $categoryCode)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/meta/category-related-metas/display-category-codes/' . $categoryCode;
        $ac = new ApiController();
        $response = $ac->getBuilder($accessKey, $secretKey, $contentType, $path);
        return $response['data']['data'];
    }
    public function getReturnCenter($accessKey, $secretKey, $vendorId)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $vendorId . '/returnShippingCenters';
        $query = 'pageNum=1';
        $ac = new ApiController();
        $response = $ac->getBuilder($accessKey, $secretKey, $contentType, $path, $query);
        $returnCenter = '';
        if ($response['status'] === true) {
            $data = $response['data']['data'];
            $trueOutbounds = $data['content'];
            foreach ($trueOutbounds as $item) {
                if ($item['usable'] === true) {
                    $returnCenter = $item;
                    break;
                }
            }
        }
        if ($returnCenter !== '') {
            return [
                'status' => true,
                'data' => $returnCenter
            ];
        }
        return [
            'status' => false,
            'message' => '쿠팡윙에서 반품지 주소를 올바르게 설정해주세요.<br>혹은 쿠팡 API 에 43.200.252.11 IP 주소를 기입해주세요.',
            'error' => $response
        ];
    }
    public function getOutbound($accessKey, $secretKey)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/marketplace_openapi/apis/api/v1/vendor/shipping-place/outbound';
        $query = 'pageNum=1';
        $ac = new ApiController();
        $response = $ac->getBuilder($accessKey, $secretKey, $contentType, $path, $query);
        $outboundCode = '';
        if ($response['status'] === true) {
            $data = $response['data'];
            $trueOutbounds = $data['content'];
            foreach ($trueOutbounds as $item) {
                if ($item['usable'] === true) {
                    $outboundCode = $item['outboundShippingPlaceCode'];
                    break;
                }
            }
        }
        if ($outboundCode !== '') {
            return [
                'status' => true,
                'data' => $outboundCode
            ];
        }
        return [
            'status' => false,
            'message' => '쿠팡윙에서 출고지 주소를 올바르게 설정해주세요.',
            'error' => $response
        ];
    }
    protected function generateData($product, $account, $outboundCode, $returnCenter, $salePrice, $shippingFee, $productPrice)
    {
        $optionName = '단일 상품';
        if ($product->hasOption === 'Y') {
            $optionName = $this->extractOptionName($product->productDetail);
        }
        $deliveryChargeType = "NOT_FREE";
        if ($productPrice >= 5000) {
            $deliveryChargeType = "FREE";
        }
        $deliveryCharge = $shippingFee;
        return [
            'displayCategoryCode' => $product->code,
            'sellerProductName' => $product->productName,
            'vendorId' => $account->code,
            'saleStartedAt' => date("Y-m-d\TH:i:s"),
            'saleEndedAt' => date("2099-12-31\TH:i:s"),
            'displayProductName' => $product->productName,
            'brand' => '제이에스',
            'generalProductName' => $product->productName,
            'deliveryMethod' => 'SEQUENCIAL',
            'deliveryCompanyCode' => 'HYUNDAI',
            'deliveryChargeType' => $deliveryChargeType,
            'deliveryCharge' => $deliveryCharge,
            'freeShipOverAmount' => 0,
            'deliveryChargeOnReturn' => $product->shipping_fee,
            'remoteAreaDeliverable' => 'Y',
            'unionDeliveryType' => 'NOT_UNION_DELIVERY',
            'returnCenterCode' => $returnCenter['returnCenterCode'],
            'returnChargeName' => $returnCenter['shippingPlaceName'],
            'companyContactNumber' => $returnCenter['placeAddresses'][0]['companyContactNumber'],
            'returnZipCode' => $returnCenter['placeAddresses'][0]['returnZipCode'],
            'returnAddress' => $returnCenter['placeAddresses'][0]['returnAddress'],
            'returnAddressDetail' => $returnCenter['placeAddresses'][0]['returnAddressDetail'],
            'returnCharge' => $product->shipping_fee,
            'outboundShippingPlaceCode' => $outboundCode,
            'vendorUserId' => $account->username,
            'requested' => true,
            'items' => [
                [
                    'itemName' => $optionName,
                    'originalPrice' => $salePrice,
                    'salePrice' => $salePrice,
                    'maximumBuyCount' => 9999,
                    'maximumBuyForPerson' => 0,
                    'maximumBuyForPersonPeriod' => 1,
                    'outboundShippingTimeDay' => 1,
                    'unitCount' => 0,
                    'adultOnly' => 'EVERYONE',
                    'taxType' => 'TAX',
                    'parallelImported' => 'NOT_PARALLEL_IMPORTED',
                    'overseasPurchased' => 'NOT_OVERSEAS_PURCHASED',
                    'pccNeeded' => false,
                    'externalVendorSku' => $product->productCode,
                    'searchTags' => explode(',', $product->productKeywords),
                    'images' => [
                        [
                            'imageOrder' => 0,
                            'imageType' => 'REPRESENTATION',
                            'vendorPath' => $product->productImage
                        ]
                    ],
                    'notices' => [
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '품명 및 모델명',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '인증/허가 사항',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '제조국(원산지)',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '제조자(수입자)',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '소비자상담 관련 전화번호',
                            'content' => '상세페이지 참조'
                        ],
                    ],
                    'contents' => [
                        [
                            'contentsType' => 'HTML',
                            'contentDetails' => [
                                [
                                    'content' => $product->productDetail,
                                    'detailType' => 'TEXT'
                                ]
                            ]
                        ]
                    ],
                    'offerCondition' => 'NEW',
                    'manufacture' => '제이에스',
                    'attributes' => []
                ]
            ]
        ];
    }
    public function extractOptionName($productDetail)
    {
        $encodedHtml = mb_convert_encoding($productDetail, 'HTML-ENTITIES', 'UTF-8');

        $doc = new DOMDocument();
        libxml_use_internal_errors(true); // HTML5 태그 등의 경고를 무시합니다.
        $doc->loadHTML($encodedHtml);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $h1Elements = $xpath->query('//h1');
        $optionName = "단일 상품";
        if ($h1Elements->length > 0) {
            $optionName = trim($h1Elements->item(0)->textContent);
        }
        return $optionName;
    }
}
