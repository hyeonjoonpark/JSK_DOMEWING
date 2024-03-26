<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        date_default_timezone_set("GMT+0");

        $datetime = date("ymd") . 'T' . date("His") . 'Z';
        $method = "POST";
        $path = "/v2/providers/seller_api/apis/api/v1/marketplace/seller-products";

        $message = $datetime . $method . $path;

        //replace with your own accessKey
        $ACCESS_KEY = "****";
        //replace with your own secretKey
        $SECRET_KEY = "****";
        $algorithm = "HmacSHA256";

        $signature = hash_hmac('sha256', $message, $SECRET_KEY);

        $authorization  = "CEA algorithm=HmacSHA256, access-key=" . $ACCESS_KEY . ", signed-date=" . $datetime . ", signature=" . $signature;

        $url = 'https://api-gateway.coupang.com' . $path;

        $strjson = `
	{
		"displayCategoryCode": 56137,
		"sellerProductName": "상품등록_example",
		"vendorId": "A00012345",
		"saleStartedAt": "2018-08-13T00:00:00",
		"saleEndedAt": "2099-01-01T23:59:59",
		"displayProductName": "해피바스 솝베리 클렌징 오일",
		"brand": "해피바스",
		"generalProductName": "솝베리 클렌징 오일",
		"productGroup": "클렌징 오일",
		"deliveryMethod": "SEQUENCIAL",
		"deliveryCompanyCode": "KGB",
		"deliveryChargeType": "FREE",
		"deliveryCharge": 0,
		"freeShipOverAmount": 0,
		"deliveryChargeOnReturn": 2500,
		"remoteAreaDeliverable": "N",
		"unionDeliveryType": "UNION_DELIVERY",
		"returnCenterCode": "1000274592",
		"returnChargeName": "반품지_1",
		"companyContactNumber": "02-1234-678",
		"returnZipCode": "135-090",
		"returnAddress": "서울특별시 강남구 삼성동",
		"returnAddressDetail": "333",
		"returnCharge": 2500,
		"outboundShippingPlaceCode": "74010",
		"vendorUserId": "user01",
		"requested": false,
		"items": [
			{
				"itemName": "200ml_1개",
				"originalPrice": 13000,
				"salePrice": 10000,
				"maximumBuyCount": "100",
				"maximumBuyForPerson": "0",
				"outboundShippingTimeDay": "1",
				"maximumBuyForPersonPeriod": "1",
				"unitCount": 1,
				"adultOnly": "EVERYONE",
				"taxType": "TAX",
				"parallelImported": "NOT_PARALLEL_IMPORTED",
				"overseasPurchased": "NOT_OVERSEAS_PURCHASED",
				"pccNeeded": "false",
				"externalVendorSku": "0001",
				"barcode": "",
				"emptyBarcode": true,
				"emptyBarcodeReason": "상품확인불가_바코드없음사유",
				"modelNo": "1717171",
				"extraProperties": null,
				"certifications": [
					{
						"certificationType": "NOT_REQUIRED",
						"certificationCode": ""
					}
				],
				"searchTags": [
					"검색어1",
					"검색어2"
				],
				"images": [
					{
						"imageOrder": 0,
						"imageType": "REPRESENTATION",
						"vendorPath": "http://image11.coupangcdn.com/image/product/image/vendoritem/2018/06/25/3719529368/27a6b898-ff3b-4a27-b1e4-330a90c25e9c.jpg"
					},
					{
						"imageOrder": 1,
						"imageType": "DETAIL",
						"vendorPath": "http://image11.coupangcdn.com/image/product/image/vendoritem/2017/02/21/3000169918/34b79649-d625-4f49-a260-b78bf7a573a8.jpg"
					},
					{
						"imageOrder": 2,
						"imageType": "DETAIL",
						"vendorPath": "http://image11.coupangcdn.com/image/product/image/vendoritem/2018/06/28/3000169918/5716aa61-70bd-47cd-8f3d-f3d49e7f496d.jpg"
					}
				],
				"notices": [
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "용량(중량)",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "제품 주요 사양",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "사용기한 또는 개봉 후 사용기간",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "사용방법",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "제조업자 및 제조판매업자",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "제조국",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "화장품법에 따라 기재, 표시하여야 하는 모든 성분",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "식품의약품안전처 심사 필 유무",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "사용할 때 주의사항",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "품질보증기준",
						"content": "제품 이상 시 공정거래위원회 고시 소비자분쟁해결기준에 의거 보상합니다."
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "소비자상담관련 전화번호",
						"content": "상세페이지 참조"
					}
				],
				"attributes": [
					{
						"attributeTypeName": "수량",
						"attributeValueName": "1개"
					},
					{
						"attributeTypeName": "개당 용량",
						"attributeValueName": "200ml"
					}
				],
				"contents": [
					{
						"contentsType": "TEXT",
						"contentDetails": [
							{
								"content": "<img src='http://image11.coupangcdn.com/image/product/content/vendorItem/2018/06/26/196713/738d905f-ed80-4fd8-ad21-ed87b195a19e.jpg'>",
								"detailType": "TEXT"
							}
						]
					}
				],
				"offerCondition": "NEW",
				"offerDescription": ""
			},
			{
				"itemName": "200ml_2개",
				"originalPrice": 26000,
				"salePrice": 20000,
				"maximumBuyCount": "100",
				"maximumBuyForPerson": "0",
				"outboundShippingTimeDay": "2",
				"maximumBuyForPersonPeriod": "1",
				"unitCount": 1,
				"adultOnly": "EVERYONE",
				"taxType": "TAX",
				"parallelImported": "NOT_PARALLEL_IMPORTED",
				"overseasPurchased": "NOT_OVERSEAS_PURCHASED",
				"pccNeeded": "false",
				"externalVendorSku": "0002",
				"barcode": "",
				"emptyBarcode": true,
				"emptyBarcodeReason": "상품확인불가_바코드없음사유",
				"modelNo": "1717172",
				"extraProperties": {
					"GOODS_SEQ":"1",
					"optuid":"18788597"
				},
				"certifications": [
					{
						"certificationType": "NOT_REQUIRED",
						"certificationCode": ""
					}
				],
				"searchTags": [
					"검색어1",
					"검색어2"
				],
				"images": [
					{
						"imageOrder": 0,
						"imageType": "REPRESENTATION",
						"vendorPath": "http://image11.coupangcdn.com/image/product/image/vendoritem/2018/06/26/3001519145/74100e2a-d1ad-4b50-9c78-840c12a3e10d.jpg"
					},
					{
						"imageOrder": 1,
						"imageType": "DETAIL",
						"vendorPath": "http://image11.coupangcdn.com/image/product/image/vendoritem/2017/02/21/3000169918/34b79649-d625-4f49-a260-b78bf7a573a8.jpg"
					},
					{
						"imageOrder": 2,
						"imageType": "DETAIL",
						"vendorPath": "http://image11.coupangcdn.com/image/product/image/vendoritem/2018/06/28/3000169918/5716aa61-70bd-47cd-8f3d-f3d49e7f496d.jpg"
					}
				],
				"notices": [
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "용량(중량)",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "제품 주요 사양",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "사용기한 또는 개봉 후 사용기간",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "사용방법",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "제조업자 및 제조판매업자",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "제조국",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "화장품법에 따라 기재, 표시하여야 하는 모든 성분",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "식품의약품안전처 심사 필 유무",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "사용할 때 주의사항",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "품질보증기준",
						"content": "상세페이지 참조"
					},
					{
						"noticeCategoryName": "화장품",
						"noticeCategoryDetailName": "소비자상담관련 전화번호",
						"content": "상세페이지 참조"
					}
				],
				"attributes": [
					{
						"attributeTypeName": "수량",
						"attributeValueName": "2개"
					},
					{
						"attributeTypeName": "개당 용량",
						"attributeValueName": "200ml"
					}
				],
				"contents": [
					{
						"contentsType": "TEXT",
						"contentDetails": [
							{
								"content": "<img src='http://image11.coupangcdn.com/image/product/content/vendorItem/2018/06/26/196713/738d905f-ed80-4fd8-ad21-ed87b195a19e.jpg'>",
								"detailType": "TEXT"
							}
						]
					}
				],
				"offerCondition": "NEW",
				"offerDescription": ""
			}
		],
		"requiredDocuments": [
			{
				"templateName": "기타인증서류",
				"vendorDocumentPath": "http://image11.coupangcdn.com/image/product/content/vendorItem/2018/07/02/41579010/eebc0c30-8f35-4a51-8ffd-808953414dc1.jpg"
			}
		],
		"extraInfoMessage": "",
		"manufacture": "아모레퍼시픽"
	}
`;

        print nl2br($strjson);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:" . $authorization));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $strjson);
        $result = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        echo ($httpcode);

        echo ($result);
    }
}
