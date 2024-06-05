<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UploadController extends Controller
{
    /**
     * @param Collection $products
     * @param Collection $partner
     * @param Collection $account
     */
    public function main($products, $partner, $account): array
    {
        $ac = new ApiController();
        $apiKey = $account->access_key;
        $method = 'post';
        $url = 'http://api.11st.co.kr/rest/prodservices/product';
        $outboundCode = $this->getOutboundCode($apiKey);
        return $outboundCode;
        foreach ($products as $product) {
            $data = $this->getData($product);
            $builderResult = $ac->builder($apiKey, $method, $url, $data);
        }
        return [
            'status' => false,
            'data' => $builderResult
        ];
    }
    private function getOutboundCode($apiKey)
    {
        $ac = new ApiController();
        $method = 'get';
        $url = 'http://api.11st.co.kr/rest/areaservice/outboundarea';
        $builderResult = $ac->builder($apiKey, $method, $url);
        return $builderResult;
    }
    private function getData($product)
    {
        return <<<_EOT_
        <?xml version="1.0" encoding="euc-kr" ?>
        <Product>
            <selMthdCd>01</selMthdCd>
            <dispCtgrNo>$product->code</dispCtgrNo>
            <prdTypCd>01</prdTypCd>
            <prdNm>$product->productName</prdNm>
            <brand>JS</brand>
            <rmaterialTypCd>05</rmaterialTypCd>
            <orgnTypCd>03</orgnTypCd>
            <suplDtyfrPrdClfCd>01</suplDtyfrPrdClfCd>
            <prdStatCd>01</prdStatCd>
            <minorSelCnYn>Y</minorSelCnYn>
            <prdImage01>$product->productImage</prdImage01>
            <htmlDetail>$product->productDetail</htmlDetail>
            <selPrc>$product->productPrice</selPrc>
            <dlvCnAreaCd>01</dlvCnAreaCd>
            <dlvWyCd>01</dlvWyCd>
            <dlvCstInstBasiCd>03</dlvCstInstBasiCd>
            <PrdFrDlvBasiAmt>300000</PrdFrDlvBasiAmt>
            <bndlDlvCnYn>N</bndlDlvCnYn>
            <dlvCstPayTypCd>03</dlvCstPayTypCd>
            <jejuDlvCst>$product->additional_shipping_fee</jejuDlvCst>
            <islandDlvCst>$product->additional_shipping_fee</islandDlvCst>
        </Product>
        _EOT_;
    }
}
