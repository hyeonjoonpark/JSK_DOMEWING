<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $inboundCode = $this->getInboundCode($apiKey);
        if ($outboundCode['status'] === false) {
            return $outboundCode;
        }
        $addrSeq = $outboundCode['data']['addrSeq'];
        $inboundCode = $inboundCode['data']['addrSeq'];
        $success = 0;
        $error = [];
        $duplicated = [];
        foreach ($products as $product) {
            $exists = DB::table('st11_uploaded_products')
                ->where('is_active', 'Y')
                ->where('st11_account_id', $account->id)
                ->where('product_id', $product->id)
                ->exists();
            if ($exists === true) {
                $duplicated[] = $product->productCode;
                continue;
            }
            $data = $this->getData($product, $addrSeq, $inboundCode);
            try {
                $builderResult = $ac->builder($apiKey, $method, $url, $data);
                if ($builderResult['status'] === false) {
                    $error[] = $builderResult['error'];
                    continue;
                }
                $resultCode = (int)$builderResult['data']->resultCode;
                if ($resultCode !== 200 && $resultCode !== 210) {
                    $error[] = $builderResult;
                    continue;
                }
                $originProductNo = $builderResult['data']->productNo;
                $storeResult = $this->store($account->id, $product->id, $product->productPrice, $product->shipping_fee, $originProductNo, $product->productName);
                if ($storeResult['status'] === false) {
                    $error[] = $storeResult['error'];
                    continue;
                }
                $success++;
            } catch (\Exception $e) {
                $error[] = $e->getMessage();
                continue;
            }
        }
        return [
            'status' => true,
            'message' => "총 " . count($products) . " 개의 상품들 중 $success 개의 상품을 성공적으로 업로드했습니다.<br>" . count($duplicated) . "개의 중복 상품을 필터링했습니다.",
            'error' => $error
        ];
    }
    private function store($st11AccountId, $productId, $price, $shippingFee, $originProductNo, $productName)
    {
        try {
            DB::table('st11_uploaded_products')
                ->insert([
                    'st11_account_id' => $st11AccountId,
                    'product_id' => $productId,
                    'price' => $price,
                    'shipping_fee' => $shippingFee,
                    'origin_product_no' => $originProductNo,
                    'product_name' => $productName
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    private function getOutboundCode($apiKey)
    {
        $ac = new ApiController();
        $method = 'get';
        $url = 'http://api.11st.co.kr/rest/areaservice/outboundarea';
        $builderResult = $ac->builder($apiKey, $method, $url);
        if ($builderResult['status'] === false) {
            return $builderResult;
        }
        try {
            $addrSeq = $builderResult['data']->xpath('//ns2:inOutAddress')[0]->addrSeq;
            return [
                'status' => true,
                'data' => [
                    'addrSeq' => $addrSeq
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '출고지 주소를 확인해주세요.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function getInboundCode($apiKey)
    {
        $ac = new ApiController();
        $method = 'get';
        $url = 'http://api.11st.co.kr/rest/areaservice/inboundarea';
        $builderResult = $ac->builder($apiKey, $method, $url);
        if ($builderResult['status'] === false) {
            return $builderResult;
        }
        try {
            $addrSeq = $builderResult['data']->xpath('//ns2:inOutAddress')[0]->addrSeq;
            return [
                'status' => true,
                'data' => [
                    'addrSeq' => $addrSeq
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '출고지 주소를 확인해주세요.',
                'error' => $e->getMessage()
            ];
        }
    }
    private function getData($product, $addrSeqOut, $inboundCode)
    {
        $aplBgnDy = date('Y/m/d');
        $aplEndDy = date('Y/m/d', strtotime('+3 year'));
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
            <sellerPrdCd>$product->productCode</sellerPrdCd>
            <orgnNmVal>기타</orgnNmVal>
            <suplDtyfrPrdClfCd>01</suplDtyfrPrdClfCd>
            <prdStatCd>01</prdStatCd>
            <minorSelCnYn>Y</minorSelCnYn>
            <prdImage01>$product->productImage</prdImage01>
            <htmlDetail><![CDATA[$product->productDetail]]></htmlDetail>
            <selPrc>$product->productPrice</selPrc>
            <dlvCnAreaCd>01</dlvCnAreaCd>
            <dlvWyCd>01</dlvWyCd>
            <dlvCstInstBasiCd>03</dlvCstInstBasiCd>
            <PrdFrDlvBasiAmt>300000</PrdFrDlvBasiAmt>
            <bndlDlvCnYn>N</bndlDlvCnYn>
            <dlvCstPayTypCd>03</dlvCstPayTypCd>
            <jejuDlvCst>$product->additional_shipping_fee</jejuDlvCst>
            <islandDlvCst>$product->additional_shipping_fee</islandDlvCst>
            <addrSeqOut>$addrSeqOut</addrSeqOut>
            <addrSeqIn>$inboundCode</addrSeqIn>
            <rtngdDlvCst>$product->shipping_fee</rtngdDlvCst>
            <exchDlvCst>$product->shipping_fee</exchDlvCst>
            <asDetail>.</asDetail>
            <rtngExchDetail>.</rtngExchDetail>
            <dlvClf>02</dlvClf>
            <ProductNotification>
                <type>891045</type>
                <item>
                    <code>23759100</code>
                    <name>상세정보 참조</name>
                </item>
                <item>
                    <code>23756033</code>
                    <name>상세정보 참조</name>
                </item>
                <item>
                    <code>11905</code>
                    <name>상세정보 참조</name>
                </item>
                <item>
                    <code>23760413</code>
                    <name>상세정보 참조</name>
                </item>
                <item>
                    <code>11800</code>
                    <name>상세정보 참조</name>
                </item>
            </ProductNotification>
            <dlvCst1>$product->shipping_fee</dlvCst1>
            <selTermUseYn>N</selTermUseYn>
            <prdSelQty>9999</prdSelQty>
            <ProductCertGroup>
                <crtfGrpTypCd>01</crtfGrpTypCd>
                <crtfGrpObjClfCd>03</crtfGrpObjClfCd>
            </ProductCertGroup>
            <ProductCertGroup>
                <crtfGrpTypCd>02</crtfGrpTypCd>
                <crtfGrpObjClfCd>03</crtfGrpObjClfCd>
            </ProductCertGroup>
            <ProductCertGroup>
                <crtfGrpTypCd>03</crtfGrpTypCd>
                <crtfGrpObjClfCd>03</crtfGrpObjClfCd>
            </ProductCertGroup>
            <ProductCertGroup>
                <crtfGrpTypCd>04</crtfGrpTypCd>
                <crtfGrpObjClfCd>05</crtfGrpObjClfCd>
            </ProductCertGroup>
        </Product>
        _EOT_;
    }
}
