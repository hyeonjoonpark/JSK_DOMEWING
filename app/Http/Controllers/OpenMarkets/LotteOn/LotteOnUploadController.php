<?php

namespace App\Http\Controllers\OpenMarkets\LotteOn;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use stdClass;

class LotteOnUploadController extends Controller
{
    private $products, $partner, $account, $loac;

    /**
     * LotteOnUploadController 의 생성자입니다.
     *
     * @param Collection $products
     * @param stdClass $partner
     * @param stdClass $account
     */
    public function __construct(Collection $products, stdClass $partner, stdClass $account)
    {
        $this->products = $products;
        $this->partner = $partner;
        $this->account = $account;
        $this->loac = new LotteOnApiController();
    }

    /**
     * 롯데온 상품 업로드 메인 메소드입니다.
     *
     * @return array
     */
    public function main(): array
    {
        $getPreDataResult = $this->getPreData();

        if (!$getPreDataResult['status']) {
            return $getPreDataResult;
        }

        $preData = $getPreDataResult['data'];
        $processProductsResult = $this->processProducts($preData);

        return $processProductsResult;
    }

    /**
     * 상품들을 가공하고 업로드합니다.
     *
     * @param array $preData
     * @return array
     */
    public function processProducts(array $preData): array
    {
        $errors = [];
        $success = 0;

        foreach ($this->products as $product) {
            $processProductResult = $this->processProduct($product, $preData);

            if (!$processProductResult['status']) {
                $errors[] = $processProductResult;
                continue;
            }

            $productData['spdLst'] = $processProductResult['data'];
            $uploadResult = $this->upload($productData);
            $errors[] = $uploadResult;
        }

        return [
            'status' => true,
            'message' => '테스트',
            'error' => $errors
        ];
    }

    /**
     * 상품 데이터를 전처리합니다.
     */
    protected function processProduct(stdClass $product, array $preData)
    {
        $isExisting = $this->isExisting($product->id);

        if ($isExisting) {
            return [
                'status' => false,
                'message' => '이미 업로드된 상품입니다.',
                'productCode' => $product->productCode
            ];
        }

        $getCategoryCodesResult = $this->getCategoryCodes($product->categoryID);

        if (!$getCategoryCodesResult['status']) {
            return [
                'status' => false,
                'message' => '전시 카테고리를 매칭하는 과정에서 오류가 발생했습니다.',
                'productCode' => $product->productCode,
            ];
        }

        $categoryCodes = $getCategoryCodesResult['data'];

        return [
            'status' => true,
            'data' => $this->generateProductData($preData, $categoryCodes, $product)
        ];
    }

    /**
     * 파트너가 이미 업로드한 상품인지 여부를 체크합니다.
     *
     * @param int @productId
     * @return bool
     */
    protected function isExisting(int $productId): bool
    {
        return DB::table('lotte_on_uploaded_products AS up')
            ->join('minewing_products AS mp', 'mp.id', '=', 'up.product_id')
            ->join('lotte_on_accounts AS a', 'a.id', '=', 'up.lotte_on_account_id')
            ->where('mp.id', $productId)
            ->where('a.partner_id', $this->partner->id)
            ->exists();
    }

    /**
     * 가공된 상품 데이터들을 롯데온에 업로드 요청을 보냅니다.
     *
     * @param array $productsData
     * @return array
     */
    protected function upload(array $productsData): array
    {
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/product/v1/product/registration/request';
        $builderResult = $this->loac->builder($method, $this->account->access_key, $url, $productsData);

        if (!$builderResult['status']) {
            return $builderResult;
        }

        return $builderResult;
    }

    /**
     * 상품의 전시 카테고리 매칭 정보를 요청합니다.
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategoryCodes(int $categoryId): array
    {
        $lotteonCategoryCode = $this->getLotteonCategoryCode($categoryId);
        $method = 'get';
        $url = 'https://onpick-api.lotteon.com/cheetah/econCheetah.ecn';
        $data = [
            'filter_1' => $lotteonCategoryCode,
            'job' => 'cheetahStandardCategory'
        ];
        $builderResult = $this->loac->builder($method, $this->account->access_key, $url, $data);

        if (!$builderResult['status']) {
            return $builderResult;
        }

        $builderData = $builderResult['data'];

        return $this->processCategoryCodes($builderData, $lotteonCategoryCode);
    }

    /**
     * 상품 전시 카테고리 매칭 정보를 가공합니다.
     *
     * @param array $builderData
     * @param string $lotteonCategoryCode
     * @return array
     */
    private function processCategoryCodes(array $builderData, string $lotteonCategoryCode): array
    {
        if (!isset($builderData['itemList'][0]['data']['disp_list'][0]['disp_cat_id'])) {
            return [
                'status' => false,
                'error' => $builderData
            ];
        }
        return [
            'status' => true,
            'data' => [
                'displayCategoryCode' => $builderData['itemList'][0]['data']['disp_list'][0]['disp_cat_id'],
                'lotteonCategoryCode' => $lotteonCategoryCode
            ]
        ];
    }

    /**
     * categoryID 를 카테고리 매핑을 통해 롯데온 표준 카테고리 코드를 조회합니다.
     *
     * @param int $ownerclanCategoryId
     * @return string
     */
    private function getLotteonCategoryCode(int $ownerclanCategoryId): string
    {
        return DB::table('category_mapping AS cm')
            ->join('lotte_on_category AS loc', 'loc.id', '=', 'cm.lotte_on')
            ->where('cm.ownerclan', $ownerclanCategoryId)
            ->value('loc.code');
    }

    /**
     * 롯데온 상품 구조체를 작성합니다.
     *
     * @param array $preData
     * @param array $categoryCodes
     * @param stdClass $product
     * @return array
     */
    private function generateProductData(array $preData, array $categoryCodes, stdClass $product): array
    {
        $prdByMaxPurPsbQtyYn = $product->bundle_quantity > 0 ? 'Y' : 'N';

        return [
            'trGrpCd' => 'SR',
            'trNo' => $this->account->partner_code,
            'scatNo' => $categoryCodes['lotteonCategoryCode'],
            'dcatLst' => [
                [
                    'mallCd' => 'LTON',
                    'lfDcatNo' => $categoryCodes['displayCategoryCode']
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
            'owhpNo' => $preData['addressCodes']['outboundCode'],
            'dvCstPolNo' => $preData['shippingPolitics']['dvCst'],
            'adtnDvCstPolNo' => $preData['shippingPolitics']['adtnDvCst'],
            'rtrpNo' => $preData['addressCodes']['inboundCode'],
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
        ];
    }

    /**
     * 출고지, 회수지, 배송 정책 등 사전 정보들을 수집합니다.
     *
     * @return array
     */
    private function getPreData(): array
    {
        $getAddressCodesResult = $this->getAddressCodes();

        if (!$getAddressCodesResult['status']) {
            return $getAddressCodesResult;
        }

        $addressCodes = $getAddressCodesResult['data'];
        $getShippingPoliticsResult = $this->getShippingPolitics();

        if (!$getShippingPoliticsResult['status']) {
            return $getShippingPoliticsResult;
        }

        $shippingPolitics = $getShippingPoliticsResult['data'];

        return [
            'status' => true,
            'data' => [
                'addressCodes' => $addressCodes,
                'shippingPolitics' => $shippingPolitics
            ]
        ];
    }

    /**
     * 계정의 출고지 및 반품지 코드들을 요청합니다.
     *
     * @return array
     */
    private function getAddressCodes(): array
    {
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvp/getDvpListSr';
        $data = [
            'afflTrCd' => $this->account->partner_code
        ];

        $builderResult = $this->loac->builder($method, $this->account->access_key, $url, $data);

        if (!$builderResult['status']) {
            return $builderResult;
        }

        $builderData = $builderResult['data'];

        if (!$builderData) {
            return [
                'status' => false,
                'message' => '셀윙 이용 가이드를 따라 출고지 및 회수지를 설정해주세요.',
                'error' => $builderData
            ];
        }

        return $this->processAddressCodes($builderData);
    }

    /**
     * 출고지 및 반품지 응답 값을 가공합니다.
     *
     * @param array $builderData
     * @return array
     */
    private function processAddressCodes(array $builderData): array
    {
        if ((int)$builderData['returnCode'] !== 0000) {
            return [
                'status' => false,
                'message' => $builderData['message'],
                'error' => $builderData
            ];
        }

        foreach ($builderData['data'] as $data) {
            switch ($data['dvpTypCd']) {
                case '01':
                    $inboundCode = $data['dvpNo'];
                    break;
                case '02':
                    $outboundCode = $data['dvpNo'];
                    break;
                default:
                    break;
            }
        }

        if (!$inboundCode || !$outboundCode) {
            return [
                'status' => false,
                'message' => '셀윙 이용 가이드를 따라 출고지 및 회수지를 설정해주세요.',
                'error' => $data
            ];
        }

        return [
            'status' => true,
            'data' => [
                'inboundCode' => $inboundCode,
                'outboundCode' => $outboundCode
            ]
        ];
    }

    /**
     * 계정의 배송 정책들을 요청합니다.
     *
     * @return array
     */
    private function getShippingPolitics(): array
    {
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/contract/v1/dvl/getDvCstListSr';
        $data = [
            'afflTrCd' => $this->account->partner_code
        ];

        $builderResult = $this->loac->builder($method, $this->account->access_key, $url, $data);

        if (!$builderResult['status']) {
            return $builderResult;
        }

        $builderData = $builderResult['data'];

        if (!$builderData) {
            return [
                'status' => false,
                'message' => '셀윙 이용 가이드를 따라 배송 정책을 설정해주세요.',
                'error' => $builderData
            ];
        }

        return $this->processShippingPolitics($builderData);
    }

    /**
     * 빌더 데이터를 기반으로 배송 정책들을 추출합니다.
     *
     * @param array $builderData
     * @return array
     */
    private function processShippingPolitics(array $builderData): array
    {
        if ((int)$builderData['returnCode'] !== 0000) {
            return [
                'status' => false,
                'message' => $builderData['message'],
                'error' => $builderData
            ];
        }

        $dvCst = null;
        $adtnDvCst = null;

        foreach ($builderData['data'] as $data) {
            switch ($data['dvCstTypCd']) {
                case 'DV_CST':
                    if (!$dvCst) {
                        $dvCst = $data['dvCstPolNo'];
                    }
                    break;
                case 'ADTN_DV_CST':
                    if (!$adtnDvCst) {
                        $adtnDvCst = $data['dvCstPolNo'];
                    }
                    break;
                default:
                    break;
            }
        }

        if (!$dvCst || !$adtnDvCst) {
            return [
                'status' => false,
                'message' => '셀윙 이용 가이드를 따라 배송 정책을 설정해주세요.',
                'error' => $data
            ];
        }

        return [
            'status' => true,
            'data' => [
                'dvCst' => $dvCst,
                'adtnDvCst' => $adtnDvCst
            ]
        ];
    }
}
