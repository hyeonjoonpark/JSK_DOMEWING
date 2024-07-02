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
    }
    protected function store($lotteOnAccountId, $productId, $price, $shippingFee, $originProductNo, $productName)
    {
        DB::table('lotte_on_uploaded_products')
            ->insert([
                'lotte_on_account_id' => $lotteOnAccountId,
                'product_id' => $productId,
                'price' => $price,
                'shipping_fee' => $shippingFee,
                'origin_product_no' => $originProductNo,
                'product_name' => $productName
            ]);
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
    private function getDispCatId($ac, $account, $categoryCode) //카테고리 조회 이젠 필요없음
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
    private function getAddresses($ac, $account) // 출고지 반품지 조회
    {
        // JSON 데이터를 생성
        $postData = json_encode([
            'afflTrCd' => $account->partner_code
        ]);

        // API 호출
        $response = $ac->postBuilder($account->access_key, 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvp/getDvpListSr', $postData);

        // 초기화
        $returnAddress = null;
        $shippingAddress = null;

        // 응답 데이터 처리
        if (isset($response['data']['result']['data']) && is_array($response['data']['result']['data'])) {
            foreach ($response['data']['result']['data'] as $address) {
                if ($address['dvpTypCd'] == "01") {
                    $returnAddress = $address;
                } elseif ($address['dvpTypCd'] == "02") {
                    $shippingAddress = $address;
                }
            }
            return [
                'status' => true,
                'data' => [
                    'returnAddress' => $returnAddress,
                    'shippingAddress' => $shippingAddress
                ]
            ];
        }

        return [
            'status' => false,
            'message' => '유효한 응답 데이터를 받지 못했습니다.'
        ];
    }


    private function getDvCstPolDetails($ac, $account) // 배송비정책리스트조회
    {
        // JSON 데이터를 생성
        $postData = json_encode([
            'afflTrCd' => $account->partner_code
        ]);
        // API 호출
        $response = $ac->postBuilder($account->access_key, 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvl/getDvCstListSr', $postData);
        $results = [];
        if (isset($response['data']['result']['data']) && is_array($response['data']['result']['data'])) {
            $resultData = $response['data']['result']['data'];
            foreach ($resultData as $item) {
                // dvCstTypCd에 따라 shippingFee 또는 additionalShippingFee 설정
                if ($item['dvCstTypCd'] == 'DV_CST') {
                    $results['shippingFee'] = $item['dvCst'];
                } elseif ($item['dvCstTypCd'] == 'ADTN_DV_CST') {
                    $results['additionalShippingFee'] = $item['inrmAdtnDvCst'];
                }
            }
            return [
                'status' => true,
                'data' => $results
            ];
        }
        return [
            'status' => false,
            'message' => '배송비정책 조회가 되지 않습니다.'
        ];
    }
}
