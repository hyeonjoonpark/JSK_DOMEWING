<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use DateTime;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotteOnUploadController extends Controller
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
        $ac = new LotteOnApiController();
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
                $error[] = $uploadResult;
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
        $ac = new LotteOnApiController();
        $response = $ac->getBuilder($accessKey, $secretKey, $contentType, $path);
        return $response['data']['data'];
    }
    public function getReturnCenter($accessKey, $secretKey, $vendorId)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $vendorId . '/returnShippingCenters';
        $query = 'pageNum=1';
        $ac = new LotteOnApiController();
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
        $ac = new LotteOnApiController();
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
    // protected function generateData($product, $account, $outboundCode, $returnCenter, $salePrice, $shippingFee, $productPrice)
    // {
    //     $optionName = '단일 상품';
    //     if ($product->hasOption === 'Y') {
    //         $optionName = $this->extractOptionName($product->productDetail);
    //     }
    //     $deliveryChargeType = "NOT_FREE";
    //     if ($productPrice >= 5000) {
    //         $deliveryChargeType = "FREE";
    //     }
    //     $deliveryCharge = $shippingFee;
    //     return [
    //         'displayCategoryCode' => $product->code,
    //         'sellerProductName' => $product->productName,
    //         'vendorId' => $account->code,
    //         'saleStartedAt' => date("Y-m-d\TH:i:s"),
    //         'saleEndedAt' => date("2099-12-31\TH:i:s"),
    //         'displayProductName' => $product->productName,
    //         'brand' => '제이에스',
    //         'generalProductName' => $product->productName,
    //         'deliveryMethod' => 'SEQUENCIAL',
    //         'deliveryCompanyCode' => 'HYUNDAI',
    //         'deliveryChargeType' => $deliveryChargeType,
    //         'deliveryCharge' => $deliveryCharge,
    //         'freeShipOverAmount' => 0,
    //         'deliveryChargeOnReturn' => $product->shipping_fee,
    //         'remoteAreaDeliverable' => 'Y',
    //         'unionDeliveryType' => 'NOT_UNION_DELIVERY',
    //         'returnCenterCode' => $returnCenter['returnCenterCode'],
    //         'returnChargeName' => $returnCenter['shippingPlaceName'],
    //         'companyContactNumber' => $returnCenter['placeAddresses'][0]['companyContactNumber'],
    //         'returnZipCode' => $returnCenter['placeAddresses'][0]['returnZipCode'],
    //         'returnAddress' => $returnCenter['placeAddresses'][0]['returnAddress'],
    //         'returnAddressDetail' => $returnCenter['placeAddresses'][0]['returnAddressDetail'],
    //         'returnCharge' => $product->shipping_fee,
    //         'outboundShippingPlaceCode' => $outboundCode,
    //         'vendorUserId' => $account->username,
    //         'requested' => true,
    //         'items' => [
    //             [
    //                 'itemName' => $optionName,
    //                 'originalPrice' => $salePrice,
    //                 'salePrice' => $salePrice,
    //                 'maximumBuyCount' => 9999,
    //                 'maximumBuyForPerson' => 0,
    //                 'maximumBuyForPersonPeriod' => 1,
    //                 'outboundShippingTimeDay' => 1,
    //                 'unitCount' => 0,
    //                 'adultOnly' => 'EVERYONE',
    //                 'taxType' => 'TAX',
    //                 'parallelImported' => 'NOT_PARALLEL_IMPORTED',
    //                 'overseasPurchased' => 'NOT_OVERSEAS_PURCHASED',
    //                 'pccNeeded' => false,
    //                 'externalVendorSku' => $product->productCode,
    //                 'searchTags' => explode(',', $product->productKeywords),
    //                 'images' => [
    //                     [
    //                         'imageOrder' => 0,
    //                         'imageType' => 'REPRESENTATION',
    //                         'vendorPath' => $product->productImage
    //                     ]
    //                 ],
    //                 'notices' => [
    //                     [
    //                         'noticeCategoryName' => '기타 재화',
    //                         'noticeCategoryDetailName' => '품명 및 모델명',
    //                         'content' => '상세페이지 참조'
    //                     ],
    //                     [
    //                         'noticeCategoryName' => '기타 재화',
    //                         'noticeCategoryDetailName' => '인증/허가 사항',
    //                         'content' => '상세페이지 참조'
    //                     ],
    //                     [
    //                         'noticeCategoryName' => '기타 재화',
    //                         'noticeCategoryDetailName' => '제조국(원산지)',
    //                         'content' => '상세페이지 참조'
    //                     ],
    //                     [
    //                         'noticeCategoryName' => '기타 재화',
    //                         'noticeCategoryDetailName' => '제조자(수입자)',
    //                         'content' => '상세페이지 참조'
    //                     ],
    //                     [
    //                         'noticeCategoryName' => '기타 재화',
    //                         'noticeCategoryDetailName' => '소비자상담 관련 전화번호',
    //                         'content' => '상세페이지 참조'
    //                     ],
    //                 ],
    //                 'contents' => [
    //                     [
    //                         'contentsType' => 'HTML',
    //                         'contentDetails' => [
    //                             [
    //                                 'content' => $product->productDetail,
    //                                 'detailType' => 'TEXT'
    //                             ]
    //                         ]
    //                     ]
    //                 ],
    //                 'offerCondition' => 'NEW',
    //                 'manufacture' => '제이에스',
    //                 'attributes' => []
    //             ]
    //         ]
    //     ];
    // }
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
    private function generateData($categoryCode)
    {
        $now = new DateTime();
        $slStrtDttm = $now->format('YmdHis');
        $oneYearLater = $now->modify('+1 year');
        $slEndDttm = $oneYearLater->format('YmdHis');
        return [
            "spdLst" => [
                [
                    "trGrpCd" => "거래처그룹코드",
                    "trNo" => "거래처번호",
                    "scatNo" => "표준카테고리번호",
                    "dcatLst" => [
                        [
                            "mallCd" => "LTON",
                            "lfDcatNo" => "전시카테고리번호"
                        ]
                    ],
                    "epdNo" => "업체상품번호", //productCode
                    "slTypCd" => "GNRL",
                    "pdTypCd" => "GNRL_GNRL",
                    "spdNm" => "상품명", //productName
                    "oplcCd" => "KR", //상품상세 참조로 변경 찾아야함 어디있는지
                    "tdfDvsCd" => "01",
                    "slStrtDttm" => $slStrtDttm, //현재시각 반영
                    "slEndDttm" => $slEndDttm, //현재시각으로부터 1년 뒤
                    "pdItmsInfo" => [
                        "pdItmsCd" => "38", //[38]기타(재화)로 상품품목코드하는지
                        "pdItmsArtlLst" => [ //상품품목항목목록 찾아야함
                            [
                                "pdArtlCd" => "0020",
                                "pdArtlCnts" => "색상입력값"
                            ],
                            [
                                "pdArtlCd" => "0060",
                                "pdArtlCnts" => "제조국입력"
                            ],
                            [
                                "pdArtlCd" => "0070",
                                "pdArtlCnts" => "제조자입력"
                            ],
                            [
                                "pdArtlCd" => "0080",
                                "pdArtlCnts" => "품질보증기준입력"
                            ],
                            [
                                "pdArtlCd" => "0090",
                                "pdArtlCnts" => "책임자입력"
                            ],
                            [
                                "pdArtlCd" => "0140",
                                "pdArtlCnts" => "크기입력"
                            ],
                            [
                                "pdArtlCd" => "0160",
                                "pdArtlCnts" => "품명입력"
                            ],
                            [
                                "pdArtlCd" => "0170",
                                "pdArtlCnts" => "주요 소재 입력"
                            ],
                            [
                                "pdArtlCd" => "0180",
                                "pdArtlCnts" => "구성품입력"
                            ],
                            [
                                "pdArtlCd" => "0190",
                                "pdArtlCnts" => "배송설치비용입력"
                            ],
                            [
                                "pdArtlCd" => "0200",
                                "pdArtlCnts" => "Y"
                            ]
                        ]
                    ],
                    "sftyAthnLst" => [ //인증대상아님 삭제 예정
                        [
                            "sftyAthnTypCd" => "LIFE_SUPS",
                            "sftyAthnOrgnNm" => "[방송통신기자재]적합등록",
                            "sftyAthnNo" => "1241251251"
                        ]
                    ],
                    "scatAttrLst" => [ // 표준카테고리속성목록
                        //표준카테고리에 매핑된 상품속성 입력시 하단의 항목을 입력한다.
                        [
                            "optCd" => "10594",
                            "optNm" => "커튼 재질",
                            "optValCd" => "104170",
                            "optVal" => "레이스"
                        ],
                        [
                            "optCd" => "11330",
                            "optNm" => "패턴/프린트",
                            "optValCd" => "109638",
                            "optVal" => "단색(무지)"
                        ],
                        [
                            "optCd" => "11582",
                            "optNm" => "캐노피 형태",
                            "optValCd" => "110544",
                            "optVal" => "사각형"
                        ]
                    ],
                    "purPsbQtyInfo" => [ //구매가능 수량정보
                        "itmByMinPurYn" => "Y", //단품별 취소구매여부
                        "itmByMinPurQty" => 2, //단품별취소구매수량
                        "itmByMaxPurPsbQtyYn" => "Y", //단품별최대구매가능수량여부
                        "maxPurQty" => 1000 //단품별최대구매수량
                    ],
                    "ageLmtCd" => "0",
                    "prstPckPsbYn" => "N",
                    "prstMsgPsbYn" => "N",
                    "prcCmprEpsrYn" => "N", //가격비교노출여부
                    "bookCultCstDdctYn" => "N",
                    "isbnCd" => "",
                    "brkHmapPkcpPsbYn" => "N",
                    "mvCmcoCd" => "",
                    "ctrtTypCd" => "A",
                    "pdSzInfo" => [ //배송사이즈정보 생략
                        "pdWdthSz" => "100",
                        "pdLnthSz" => "200",
                        "pdHghtSz" => "300",
                        "pckWdthSz" => "110",
                        "pckLnthSz" => "210",
                        "pckHghtSz" => "310"
                    ],
                    "pdStatCd" => "NEW",
                    "dpYn" => "Y",
                    "ltonDpYn" => "Y", //체크필요
                    "scKwdLst" => [ //productKetword넣기
                        "검색키워드1",
                        "검색키워드2",
                        "검색키워드3"
                    ],
                    "pdFileLst" => [ //중고아니라 생략
                        [
                            "fileTypCd" => "PD",
                            "fileDvsCd" => "WDTH",
                            "origFileNm" => "https://image.ellotte.com/ellt.static.lotteeps.com/goods/img/95/53/78/07/10/1007785395_mast.jpg"
                        ],
                        [
                            "fileTypCd" => "PD",
                            "fileDvsCd" => "VDO_URL",
                            "origFileNm" => "https://simage.lottemart.com/lim/static_root/images/prodimg/video/88094/3379/8809433791613.mp4"
                        ]
                    ],
                    "epnLst" => [
                        [
                            "pdEpnTypCd" => "DSCRP",
                            "cnts" => "<html>~~~</html>" //productDetail
                        ]
                    ],
                    "cnclPsbYn" => "Y", //취소가능여부인데 취소가능하게할지 불가능하게할지.
                    "dmstOvsDvDvsCd" => "DMST",
                    "pstkYn" => "N", //선재고여부?
                    "dvProcTypCd" => "LO_ENTP", //배송상품유형코드?
                    "dvPdTypCd" => "GNRL",
                    "sndBgtNday" => "0",
                    "sndBgtDdInfo" => [ //발송예정일정보 12시?
                        "nldySndCloseTm" => "1300",
                        "satSndPsbYn" => "Y", //토요일 발송가능여부 [Y, N]
                        "satSndCloseTm" => "1200"
                    ],
                    "dvRgsprGrpCd" => "GN101", //배송가능지역코드?
                    "dvMnsCd" => "DPCL",
                    "owhpNo" => "115", //출고지번호거래처 API
                    "hdcCd" => "0001", //택배사 생략예정
                    "dvCstPolNo" => "335", //배송비정책번호
                    "adtnDvCstPolNo" => "", //추가배송비정책번호
                    "cmbnDvPsbYn" => "Y", //합배송가능여부
                    "dvCstStdQty" => 0, //배송비기준수량  => 번들퀀티티
                    "qckDvUseYn" => "N",
                    "crdayDvPsbYn" => "N",
                    "hpDdDvPsbYn" => "N", //희망일배송가능여부
                    'hpDdDvPsbPrd' => 1231231322, //희망일배송가능기간
                    "saveTypCd" => "NONE",
                    "shopCnvMsgPsbYn" => "N",
                    "rgnLmtPdYn" => "N",
                    "fprdDvPsbYn" => "N",
                    "spcfSqncPdYn" => "N",
                    "rtngPsbYn" => "N", //반품가능여부
                    "xchgPsbYn" => "Y", //교환가능여부
                    "cmbnRtngPsbYn" => "Y", //합반품가능여부
                    "rtngHdcCd" => "0001", //생략예정
                    "rtngRtrvPsbYn" => "Y", //반품회수가능여부
                    "rtrvTypCd" => "ENTP_RTRV",
                    "rtrpNo" => "115",
                    "stkMgtYn" => "N", //재고관리여부 [Y, N]
                    //'N'인 경우 재고가 999,999,999로 들어간다. 웹재고를 관리하지 않는다.
                    "sitmYn" => "Y",/*
                    판매자단품여부 [Y, N]
Y이면 단품속성목록을 설정해야 한다.
N이면 단품속성목록을 설정 안한다. 옵션이 없는 단품 한가지로 설정된다.*/
                    "optSrtLst" => [
                        [
                            "optSeq" => 1,
                            "optNm" => "색상",
                            "optValSrtLst" => [
                                [
                                    "optValSeq" => 1,
                                    "optVal" => "맛있는초코색"
                                ]
                            ]
                        ]
                    ],
                    "itmLst" => [
                        [
                            "eitmNo" => "ITM_1",
                            "dpYn" => "Y",
                            "sortSeq" => 1,
                            "itmOptLst" => [
                                [
                                    "optNm" => "색상",
                                    "optVal" => "맛있는초코색"
                                ]
                            ],
                            "itmImgLst" => [
                                [
                                    "epsrTypCd" => "IMG",
                                    "epsrTypDtlCd" => "IMG_SQRE",
                                    "origImgFileNm" => "https://image.ellotte.com/ellt.static.lotteeps.com/goods/img/95/53/78/07/10/1007785395_mast.jpg",
                                    "rprtImgYn" => "Y"
                                ],
                                [
                                    "epsrTypCd" => "IMG",
                                    "epsrTypDtlCd" => "IMG_SQRE",
                                    "origImgFileNm" => "https://image.ellotte.com/ellt.static.lotteeps.com/goods/img/95/53/78/07/10/1007785395_mast.jpg",
                                    "rprtImgYn" => "N"
                                ]
                            ],
                            "pdUtStdInfo" => [
                                "pdCapa" => 10
                            ],
                            "slPrc" => 200000,
                            "stkQty" => 110
                        ]
                    ],
                    "adtnPdYn" => "Y",
                    "adtnPdInfo" => [
                        "sortCd" => "NAME_ASC",
                        "adtnTypeLst" => [
                            [
                                "adtnTypNm" => "추가유형명입니다.",
                                "epsrPrirRnkg" => 11,
                                "adtnPdLst" => [
                                    [
                                        "adtnPdNm" => "이름입니다",
                                        "epdNo" => "YSM_ADTN_NUMBER",
                                        "epsrPrirRnkg" => 12,
                                        "slPrc" => 1700,
                                        "stkQty" => 1203,
                                        "useYn" => "Y"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    private function getDispCatId($ac, $account, $categoryCode)
    {
        $response = $ac->getBuilder($account->access_key, 'https://onpick-api.lotteon.com/cheetah/econCheetah.ecn?job=cheetahStandardCategory&filter_1=' . $categoryCode);
        // JSON 데이터를 PHP 배열로 디코딩
        $data = json_decode($response, true);
        $disp_cat_ids = [];
        if (isset($data['data']['itemList'])) {
            foreach ($data['data']['itemList'] as $item) {
                if (isset($item['data']['disp_list'])) {
                    foreach ($item['data']['disp_list'] as $disp) {
                        if (isset($disp['disp_cat_id'])) {
                            $disp_cat_ids[] = $disp['disp_cat_id'];
                        }
                    }
                }
            }
        }
        // 추출한 disp_cat_id 배열 반환
        return $disp_cat_ids;
    }
    private function extractDvpNo($ac, $account, $afflTrCd)
    {
        $postData = json_encode([
            'afflTrCd' => $afflTrCd
        ]);
        $response = $ac->postBuilder($account->access_key, 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvp/getDvpListSr', $postData);
        $resultData = $response['data']['result']['data'];
        foreach ($resultData as $item) {
            if ($item['dvpTypCd'] === "01") {
                return $item['dvpNo'];
            }
        }
        return null;
    }
}
