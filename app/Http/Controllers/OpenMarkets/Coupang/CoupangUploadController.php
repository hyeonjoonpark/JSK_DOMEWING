<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
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
            $responseOutbound = $this->getOutbound($account);
            return $responseOutbound;
            // if ($responseOutbound['status'] === false) {
            //     return [
            //         'status' => false,
            //         'message' => "쿠팡윙에서 출고지 주소를 올바르게 설정해주세요."
            //     ];
            // }
            // $outboundShippingPlaceCode = $responseOutbound['data']['content']['outboundShippingPlaceCode'];
            // $data = $this->generateData($product, $account);
            // return $ac->builder($account, $contentType, $method, $path, $data);
        }
    }
    public function getOutbound($account)
    {
        $contentType = 'application/json;charset=UTF-8';
        $method = 'GET';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/vendors/' . $account->code . '/check-auto-category-agreed';
        $data = [
            'pageNum' => 1
        ];
        $ac = new ApiController();
        return $ac->builder($account, $contentType, $method, $path, $data);
    }
    protected function generateData($product, $account)
    {
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
            'deliveryChargeOnReturn' => $product->shipping_fee,
            'remoteAreaDeliverable' => 'Y',
            'unionDeliveryType' => 'NOT_UNION_DELIVERY'
        ];
    }
}
