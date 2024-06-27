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
    private function generateData($categoryCode, $dvpNo)
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
                    "slStrtDttm" => $slStrtDttm,
                    "slEndDttm" => $slEndDttm,
                    "pdItmsInfo" => [
                        "pdItmsCd" => "38",
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
                        "itmByMinPurQty" => 1, //단품별 최소구매수량
                        "itmByMaxPurPsbQtyYn" => "N", //단품별최대구매가능수량여부
                    ],
                    "ageLmtCd" => "0",
                    "prstPckPsbYn" => "N",

                    "bookCultCstDdctYn" => "N",
                    "ctrtTypCd" => "A",
                    "pdStatCd" => "NEW",
                    "dpYn" => "Y",
                    "scKwdLst" => [ //productKetword넣기
                        "검색키워드1",
                        "검색키워드2",
                        "검색키워드3"
                    ],
                    "epnLst" => [
                        [
                            "pdEpnTypCd" => "AS_CNTS",
                            "cnts" => "<html>~~~</html>" //productDetail
                        ]
                    ],
                    "cnclPsbYn" => "Y",
                    "dmstOvsDvDvsCd" => "DMST",
                    "pstkYn" => "Y",
                    "dvProcTypCd" => "LO_ENTP", //배송상품유형코드?
                    "dvPdTypCd" => "GNRL",
                    "sndBgtDdInfo" => [ //발송예정일정보 12시?
                        "nldySndCloseTm" => "0900", //평일발송마감시간
                        "satSndPsbYn" => "N",
                        "satSndCloseTm" => "1200" //토요일 발송가능여부 Y일시 토요일발송마감시간 필수
                    ],
                    "dvRgsprGrpCd" => "GN101", //배송가능지역코드??
                    "dvMnsCd" => "DPCL",
                    "owhpNo" => $dvpNo, //출고지번호거래처 API
                    "dvCstPolNo" => "335", //배송비정책번호 API
                    "adtnDvCstPolNo" => "", //추가배송비정책번호
                    "cmbnDvPsbYn" => "N", //합배송가능여부
                    "dvCstStdQty" => 0, //배송비기준수량  => 번들퀀티티
                    "rtngPsbYn" => "N", //반품가능여부
                    "xchgPsbYn" => "Y", //교환가능여부
                    "cmbnRtngPsbYn" => "N", //합반품가능여부
                    "rtrvTypCd" => "ENTP_RTRV",
                    "rtrpNo" => "115", //회수지번호 api사용
                    "stkMgtYn" => "Y",
                    "sitmYn" => "N",
                    "itmLst" => [
                        [
                            "eitmNo" => "ITM_1", //productCode?
                            "dpYn" => "Y",
                            "sortSeq" => 1,
                            "itmImgLst" => [
                                [
                                    "epsrTypCd" => "IMG",
                                    "epsrTypDtlCd" => "IMG_SQRE",
                                    "origImgFileNm" => "https://image.ellotte.com/ellt.static.lotteeps.com/goods/img/95/53/78/07/10/1007785395_mast.jpg", //productImage
                                    "rprtImgYn" => "Y"
                                ],
                                [
                                    "epsrTypCd" => "IMG",
                                    "epsrTypDtlCd" => "IMG_SQRE",
                                    "origImgFileNm" => "https://image.ellotte.com/ellt.static.lotteeps.com/goods/img/95/53/78/07/10/1007785395_mast.jpg",
                                    "rprtImgYn" => "N"
                                ]
                            ],
                            "slPrc" => 200000, //productPrice에 수수료?
                            "stkQty" => 300 //재고수량 300통일
                        ]
                    ],
                    "adtnPdYn" => "N",
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
    private function getDvpNo($ac, $account)
    {
        // JSON 데이터를 생성
        $postData = json_encode([
            'afflTrCd' => $account->partner_code
        ]);
        // API 호출
        $response = $ac->postBuilder($account->access_key, 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvp/getDvpListSr', $postData);
        if (isset($response['data']['result']['data']) && is_array($response['data']['result']['data'])) {
            $resultData = $response['data']['result']['data'];
            foreach ($resultData as $item) {
                if ($item['dvpTypCd'] === "01") {
                    return [
                        'status' => true,
                        'data' => $item['dvpNo']
                    ];
                }
            }
        }
        return [
            'status' => false,
            'message' => '유효한 응답 데이터를 받지 못했습니다.'
        ];
    }

    private function getDvCstPolDetails($ac, $account)
    {
        // JSON 데이터를 생성
        $postData = json_encode([
            'afflTrCd' => $account->partner_code
        ]);

        // API 호출
        $response = $ac->postBuilder($account->access_key, 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvl/getDvCstListSr', $postData);

        // 결과를 담을 배열 초기화
        $results = [];

        // 응답 데이터가 유효한지 확인
        if (isset($response['data']['result']['data']) && is_array($response['data']['result']['data'])) {
            $resultData = $response['data']['result']['data'];

            // 각 항목을 반복하여 처리
            foreach ($resultData as $item) {
                $dvCstPolNo = isset($item['dvCstPolNo']) ? $item['dvCstPolNo'] : null;
                $inrmAdtnDvCst = isset($item['inrmAdtnDvCst']) ? $item['inrmAdtnDvCst'] : null;

                $results[] = [
                    'dvCstPolNo' => $dvCstPolNo,
                    'inrmAdtnDvCst' => $inrmAdtnDvCst
                ];
            }

            return [
                'status' => true,
                'data' => $results
            ];
        }

        // 유효한 데이터를 찾지 못한 경우
        return [
            'status' => false,
            'message' => '배송비정책 조회가 되지 않습니다.'
        ];
    }
}
