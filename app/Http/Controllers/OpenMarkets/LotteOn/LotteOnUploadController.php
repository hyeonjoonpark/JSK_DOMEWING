<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use DateTime;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    }
    protected function processProducts()
    {
        $requestOwhpNoResult = $this->requestOwhpNo();
        $requestDvCstPolNoResult = $this->requestDvCstPolNo();
        if (!$requestOwhpNoResult['status']) {
            return [
                'status' => false,
                'message' => '해당 판매자 계정의 출고지 조회에 실패했습니다.'
            ];
        }
        $OwhpNo = $requestOwhpNoResult['data'];
        $errors = [];
        foreach ($this->products as $product) {
            $generateDataResult = $this->generateData($product, $OwhpNo);
            if (!$generateDataResult) {
                $errors[] = [
                    'productCode' => $product->productCode,
                    'message' => $generateDataResult['message'],
                    'error' => $generateDataResult['error']
                ];
            }
            $data['spdLst'][] = $generateDataResult;
        }
        return $data;
    }
    protected function generateData(Collection $product, string $OwhpNo)
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
            'owhpNo' => $OwhpNo,
            'hdcCd' => '0001'
        ];
    }
    public function requestOwhpNo()
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
                'status' => false
            ];
        }
        $filteredResult = array_filter($builderResult['data']['data'], function ($item) {
            return $item['dvpTypCd'] === '02';
        });
        if (empty($filteredResult)) {
            return [
                'status' => false
            ];
        }
        $filteredResult = array_values($filteredResult);
        return [
            'status' => true,
            'data' => $filteredResult[0]['dvpNo']
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
        return $builderResult;
        if (!isset($builderResult['data']['data'])) {
            return [
                'status' => false
            ];
        }
        $filteredResult = array_filter($builderResult['data']['data'], function ($item) {
            return $item['dvpTypCd'] === '02';
        });
        if (empty($filteredResult)) {
            return [
                'status' => false
            ];
        }
        $filteredResult = array_values($filteredResult);
        return [
            'status' => true,
            'data' => $filteredResult[0]['dvCstPolNo']
        ];
    }
}
