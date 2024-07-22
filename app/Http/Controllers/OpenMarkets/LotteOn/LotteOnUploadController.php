<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

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
        set_time_limit(0);
        $filterDuplicatesResult = $this->filterDuplicates();
        $processProductsResult = $this->processProducts();
        if (!$processProductsResult['status']) {
            return $processProductsResult;
        }
        $data = $processProductsResult['data'];
        $uploadResult = $this->upload($data);
        if (!$uploadResult['status']) {
            return $uploadResult;
        }
        return $this->processStore($uploadResult['data']);
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
        $errors = [];
        $data = [];
        foreach ($this->products as $product) {
            $isExisting = $this->isExisting($product);
            if ($isExisting) {
                continue;
            }
            $generateDataResult = $this->generateData($product, $warehouseAndReturnInfo, $dvCstPolNo);
            if (!$generateDataResult['status']) {
                $errors[] = [
                    'productCode' => $product->productCode,
                    'message' => $generateDataResult['message'],
                    'error' => $generateDataResult['error']
                ];
                continue;
            }
            $data['spdLst'][] = $generateDataResult['data'];
        }
        return [
            'status' => true,
            'data' => $data,
            'error' => $errors
        ];
    }
    protected function isExisting(Collection $product)
    {
        return DB::table('lotte_on_uploaded_products AS up')
            ->join('minewing_products AS mp', 'mp.id', '=', 'up.product_id')
            ->join('lotte_on_accounts AS a', 'a.id', '=', 'up.lotte_on_account_id')
            ->where('mp.id', $product->id)
            ->where('a.partner_id', $this->partner->id)
            ->exists();
    }
    protected function generateData(stdClass $product, array $warehouseAndReturnInfo, array $dvCstPolNo)
    {
        $lotteonCategoryCode = $this->getCategoryCode($product->categoryID);
        $requestDcatLstResult = $this->requestDcatLst($lotteonCategoryCode);
        if (!$requestDcatLstResult['status']) {
            return $requestDcatLstResult;
        }
        $lfDcatNo = $requestDcatLstResult['data'];
        $prdByMaxPurPsbQtyYn = $product->bundle_quantity > 0 ? 'Y' : 'N';
        return [
            'status' => true,
            'data' => [
                'trGrpCd' => 'SR',
                'trNo' => $this->account->partner_code,
                'scatNo' => $lotteonCategoryCode,
                'dcatLst' => [
                    [
                        'mallCd' => 'LTON',
                        'lfDcatNo' => $lfDcatNo
                    ]
                ],
                'epdNo' => $product->productCode,
                'slTypCd' => 'GNRL',
                'pdTypCd' => 'GNRL_GNRL',
                'spdNm' => $product->productName,
                'oplcCd' => '상품상세 참조',
                'tdfDvsCd' => '01',
                'slStrtDttm' => date('YmdHis'),
                'slEndDttm' => '20991231235959',
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
                'prstPckPsbYn' => 'N',
                'prstMsgPsbYn' => 'N',
                'pdStatCd' => 'NEW',
                'scKwdLst' => explode(',', $product->productKeywords),
                'epnLst' => [
                    [
                        'pdEpnTypCd' => 'DSCRP',
                        'cnts' => $product->productDetail
                    ]
                ],
                'dvProcTypCd' => 'LO_ENTP',
                'dvPdTypCd' => 'GNRL',
                'dvRgsprGrpCd' => 'GN101',
                'dvMnsCd' => 'DPCL',
                'owhpNo' => $warehouseAndReturnInfo['owhpNo'],
                'dvCstPolNo' => $dvCstPolNo[1],
                'adtnDvCstPolNo' => $dvCstPolNo[0],
                'rtrpNo' => $warehouseAndReturnInfo['rtrpNo'],
                'stkMgtYn' => 'N',
                'sitmYn' => 'Y',
                'itmLst' => [
                    [
                        'eitmNo' => $product->productCode,
                        'dpYn' => 'Y',
                        "sortSeq" => 1,
                        'itmOptLst' => [
                            [
                                'optNm' => '단일 상품',
                                'optVal' => '단일 상품'
                            ]
                        ],
                        'itmImgLst' => [
                            [
                                'epsrTypCd' => 'IMG',
                                'epsrTypDtlCd' => 'IMG_SQRE',
                                'origImgFileNm' => $product->productImage,
                                'rprtImgYn' => 'Y'
                            ]
                        ],
                        'slPrc' => round($product->productPrice, -1)
                    ],
                ],
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
            ->value('loc.code');
    }
    public function requestDcatLst(string $lotteonCategoryCode)
    {
        $method = 'get';
        $url = 'https://onpick-api.lotteon.com/cheetah/econCheetah.ecn';
        $data = [
            'filter_1' => $lotteonCategoryCode,
            'job' => 'cheetahStandardCategory'
        ];
        $loac = new LotteOnApiController();
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
    protected function upload(array $data)
    {
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/product/v1/product/registration/request';
        $loac = new LotteOnApiController();
        $response = $loac->builder($method, $this->account->access_key, $url, $data);
        if (!$response['status']) {
            return $response;
        }
        $productCodes = $response['data']['data'];
        return [
            'status' => true,
            'data' => $productCodes
        ];
    }
    protected function processStore(array $productCodes)
    {
        $success = 0;
        foreach ($productCodes as $productCode) {
            if ($productCode['resultCode'] !== 0000) {
                continue;
            }
            $sellwingProductCode = $productCode['epdNo'];
            $lotteonProductCode = $productCode['spdNo'];
            $storeResult = $this->store($sellwingProductCode, $lotteonProductCode);
            if ($storeResult) {
                $success++;
            }
        }
        return [
            'status' => true,
            'message' => "총 " . number_format(count($this->products)) . " 개의 상품들 중 <strong>$success</strong>개의 상품을 성공적으로 업로드했습니다.",
            'error' => '?'
        ];
    }
    protected function store(string $sellwingProductCode, string $lotteonProductCode)
    {
        $product = $this->products->firstWhere('productCode', $sellwingProductCode);
        try {
            DB::table('lotte_on_uploaded_products')
                ->insert([
                    'lotte_on_account_id' => $this->account->id,
                    'product_id' => $product->id,
                    'product_name' => $product->productName,
                    'price' => $product->productPrice,
                    'shipping_fee' => $product->shipping_fee,
                    'origin_product_no' => $lotteonProductCode
                ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
