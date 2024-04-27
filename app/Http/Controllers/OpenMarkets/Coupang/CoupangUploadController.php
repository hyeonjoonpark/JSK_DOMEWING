<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

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
        foreach ($products as $product) {
            $account = $this->account;
            $contentType = 'application/json;charset=UTF-8';
            $method = 'POST';
            $path = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products';
            $ac = new ApiController();
            $accessKey = $account->access_key;
            $secretKey = $account->secret_key;
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
            $data = $this->generateData($product, $account, $outboundCode, $returnCenter);
            return $ac->builder($accessKey, $secretKey, $method, $contentType, $path, $data);
        }
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
            'message' => '쿠팡윙에서 반품지 주소를 올바르게 설정해주세요.'
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
            'status' => true,
            'message' => '쿠팡윙에서 출고지 주소를 올바르게 설정해주세요.'
        ];
    }
    protected function generateData($product, $account, $outboundCode, $returnCenter)
    {
        $salePrice = $product->productPrice + $product->shipping_fee;
        $images = $this->processProductDetail($product->productDetail);
        $optionName = '단일 상품';
        if ($product->hasOption === 'Y') {
            $optionName = $this->extractOptionName($product->productDetail);
        }
        return [
            'displayCategoryCode' => $product->code,
            'sellerProductName' => $product->productName,
            'vendorId' => $account->code,
            'saleStartedAt' => date("yyyy-MM-dd'T'HH:mm:ss"),
            'saleEndedAt' => date("2099-MM-dd'T'HH:mm:ss"),
            'displayProductName' => $product->productName,
            'brand' => '제이에스',
            'generalProductName' => $product->productName,
            'deliveryMethod' => 'SEQUENCIAL',
            'deliveryCompanyCode' => 'HYUNDAI',
            'deliveryChargeType' => 'FREE',
            "deliveryCharge" => 0,
            "freeShipOverAmount" => 0,
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
            'vendorUserId' => $account->code,
            'requested' => true,
            'items' => [
                'itemName' => $optionName,
                'originalPrice' => $salePrice,
                'salePrice' => $salePrice,
                'maximumBuyCount' => 9999,
                'maximumBuyForPerson' => 0,
                'maximumBuyForPersonPeriod' => 1,
                'outboundShippingTimeDay' => 0,
                'unitCount' => 0,
                'adultOnly' => 'EVERYONE',
                'taxType' => 'TAX',
                'parallelImported' => 'NOT_PARALLEL_IMPORTED',
                'overseasPurchased' => 'NOT_OVERSEAS_PURCHASED',
                'pccNeeded' => false,
                'externalVendorSku' => $product->productCode,
                'searchTags' => explode(',', $product->productKeywords),
                'images' => $images,
                'notices' => [
                    'noticeCategoryName' => '기타 재화',
                ]
            ]
        ];
    }
    protected function processProductDetail($productDetail)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($productDetail);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $images = $xpath->query('//img');

        $images = [];
        foreach ($images as $index => $img) {
            $src = $img->getAttribute('src');
            $imageIndex = $index + 1;
            $images[] = [
                'imageOrder' => $imageIndex,
                'imageType' => 'DETAIL',
                'vendorPath' => $src
            ];
        }
        return $images;
    }
    protected function extractOptionName($productDetail)
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
