<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use stdClass;

use function PHPUnit\Framework\isEmpty;

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
        $processProductsResult = $this->processProducts();
        if (!$processProductsResult['status']) {
            return $processProductsResult;
        }
        $data = $processProductsResult['data'];
        $error = $processProductsResult['error'];
    }
    protected function processProducts()
    {
        $getWarehouseAndReturnInfoResult = $this->getWarehouseAndReturnInfo();
        if (!$getWarehouseAndReturnInfoResult['status']) {
            return [
                'status' => false,
                'message' => '해당 판매자 계정의 출고지|반품지 조회에 실패했습니다.'
            ];
        }
        $warehouseAndReturnInfo = $getWarehouseAndReturnInfoResult['data'];
        $requestDvCstPolNoResult = $this->requestDvCstPolNo();
        if (!$requestDvCstPolNoResult['status']) {
            return [
                'status' => false,
                'message' => '해당 판매자 계정의 배송 정책 조회에 실패했습니다.'
            ];
        }
        $dvCstPolNo = $requestDvCstPolNoResult['data'];
        return [
            'warehouseAndReturnInfo' => $warehouseAndReturnInfo,
            'dvCstPolNo' => $dvCstPolNo
        ];
        $errors = [];
        foreach ($this->products as $product) {
            $generateDataResult = $this->generateData($product, $warehouseAndReturnInfo, $dvCstPolNo);
            if (!$generateDataResult) {
                $errors[] = [
                    'productCode' => $product->productCode,
                    'message' => $generateDataResult['message'],
                    'error' => $generateDataResult['error']
                ];
            }
            $data['spdLst'][] = $generateDataResult;
        }
        return [
            'status' => true,
            'data' => $data,
            'error' => $errors
        ];
    }
    protected function generateData(stdClass $product, array $warehouseAndReturnInfo, array $dvCstPolNo)
    {
        $scatNo = $this->getCategoryCode($product->categoryID);
        $requestDcatLstResult = $this->requestDcatLst($scatNo);
        if (!$requestDcatLstResult['status']) {
            return $requestDcatLstResult;
        }
        $lfDcatNo = $requestDcatLstResult['data'];
        $prdByMaxPurPsbQtyYn = $product->bundle_quantity > 0 ? 'Y' : 'N';
        return [
            'scatNo' => $scatNo,
            'dcatLst' => [
                [
                    'mallCd' => 'LTON',
                    'lfDcatNo' => $lfDcatNo
                ]
            ],
            'prdByMaxPurPsbQtyYn' => $prdByMaxPurPsbQtyYn,
            'slTypCd' => 'GNRL',
            'pdTypCd' => 'GNRL_GNRL',
            'spdNm' => $product->productName,
            'oplcCd' => "상품상세 참조",
            'tdfDvsCd' => '01',
            'slStrtDttm' => date('YmdHis', time()),
            'slEndDttm' => '20990801100000',
            'pdItmsInfo' => [
                'pdItmsCd' => 38,
                'pdItmsArtlLst' => [
                    [
                        'pdArtlCd' => '0210',
                        'pdArtlCnts' => '상품상세 참조'
                    ],
                    [
                        'pdArtlCd' => '1400',
                        'pdArtlCnts' => '상품상세 참조'
                    ],
                    [
                        'pdArtlCd' => '1420',
                        'pdArtlCnts' => '상품상세 참조'
                    ],
                    [
                        'pdArtlCd' => '0070',
                        'pdArtlCnts' => '상품상세 참조'
                    ],
                    [
                        'pdArtlCd' => '1440',
                        'pdArtlCnts' => '상품상세 참조'
                    ],
                ]
            ],
            'purPsbQtyInfo' => [
                'itmByMinPurYn' => 'N',
                'itmByMaxPurPsbQtyYn' => $prdByMaxPurPsbQtyYn,
                'maxPurQty' => $product->bundle_quantity,
                'maxPurLmtTypCd' => 'ONCE'
            ],
            'ageLmtCd' => '0',
            'pdStatCd' => 'NEW',
            'epnLst' => [
                [
                    'pdEpnTypCd' => 'DSCRP',
                    'cnts' => $product->productDetail
                ]
            ],
            'dvProcTypCd' => 'LO_ENTP',
            'dvPdTypCd' => 'GNRL',
            'dvRgsprGrpCd' => 'DV_RGSPR_GRP_CD',
            'dvMnsCd' => 'DPCL',
            'owhpNo' => $warehouseAndReturnInfo['owhpNo'],
            'hdcCd' => '0001',
            'dvCstPolNo' => $dvCstPolNo[0],
            'adtnDvCstPolNo' => $dvCstPolNo[1],
            'cmbnDvPsbYn' => 'N',
            'dvCstStdQty' => $product->bundle_quantity,
            'rtrpNo' => $warehouseAndReturnInfo['rtrpNo'],
            'itmLst' => [
                [
                    'eitmNo' => $product->productCode,
                    'dpYn' => 'Y',
                    'itmImgLst' => [
                        [
                            'epsrTypCd' => 'IMG',
                            'epsrTypDtlCd' => 'IMG_SQRE',
                            'origImgFileNm' => $product->productImage,
                            'rprtImgYn' => 'Y'
                        ]
                    ],
                    'clrchipLst' => [
                        [
                            'origImgFileNm' => $product->productImage
                        ]
                    ],
                    'slPrc' => $product->productPrice
                ]
            ]
        ];
    }
    public function getWarehouseAndReturnInfo()
    {
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvp/getDvpListSr';
        $loac = new LotteOnApiController();
        $data = [
            'afflTrCd' => $this->account->partner_code
        ];
        $builderResult = $loac->builder($method, $this->account->access_key, $url, $data);
        if (!isset($builderResult['data']['data'])) {
            return [
                'status' => false,
                'message' => '데이터를 가져오는 데 실패했습니다.'
            ];
        }
        $owhpNo = null;
        $rtrpNo = null;
        foreach ($builderResult['data']['data'] as $br) {
            if ($br['dvpTypCd'] === '02') {
                $owhpNo = $br['dvpNo'];
            } elseif ($br['dvpTypCd'] === '01') {
                $rtrpNo = $br['dvpNo'];
            }
        }
        if (is_null($owhpNo) || is_null($rtrpNo)) {
            return [
                'status' => false,
                'message' => '해당 판매자 계정의 출고지 또는 반품지 정보를 확인해주세요.'
            ];
        }
        return [
            'status' => true,
            'data' => [
                'owhpNo' => $owhpNo,
                'rtrpNo' => $rtrpNo
            ]
        ];
    }
    protected function getCategoryCode($categoryId)
    {
        return DB::table('lotte_on_category AS loc')
            ->join('category_mapping AS cm', 'cm.lotte_on', '=', 'loc.id')
            ->where('cm.ownerclan', $categoryId)
            ->value('cm.lotte_on');
    }
    public function requestDcatLst(string $scatNo)
    {
        $method = 'get';
        $url = 'https://onpick-api.lotteon.com/cheetah/econCheetah.ecn?job=cheetahDisplayCategory';
        $loac = new LotteOnApiController();
        $data = [
            'job' => 'cheetahStandardCategory',
            'filter_1' => $scatNo
        ];
        $builderResult = $loac->builder($method, $this->account->access_key, $url, $data);
        if (!isset($builderResult['data']['itemList'][0]['data']['disp_list'][0]['disp_cat_id'])) {
            return [
                'status' => false,
                'message' => '전시 카테고리와 매칭이 실패된 상품입니다.',
                'error' => $builderResult
            ];
        }
        return [
            'status' => true,
            'data' => $builderResult['data']['itemList'][0]['data']['disp_list'][0]['disp_cat_id']
        ];
    }
    public function requestDvCstPolNo()
    {
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvl/getDvCstListSr';
        $loac = new LotteOnApiController();
        $data = [
            'afflTrCd' => $this->account->partner_code
        ];
        $builderResult = $loac->builder($method, $this->account->access_key, $url, $data);
        if (!isset($builderResult['data']['data'])) {
            return [
                'status' => false
            ];
        }
        $resultData = $builderResult['data']['data'];
        if (empty($resultData)) {
            return [
                'status' => false
            ];
        }
        $arrayDvCstPolNo = [];
        foreach ($resultData as $rd) {
            $arrayDvCstPolNo[] = $rd['dvCstPolNo'];
        }
        return [
            'status' => true,
            'data' => $arrayDvCstPolNo
        ];
    }
}
