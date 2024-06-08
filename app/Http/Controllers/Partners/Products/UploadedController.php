<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangUploadController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use App\Http\Controllers\Product\NameController;
use App\Http\Controllers\SmartStore\SmartstoreProductUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadedController extends Controller
{
    public function index(Request $request)
    {
        // 파트너 ID를 가져옵니다.
        $partnerId = Auth::guard('partner')->id();

        // 연동된 도매윙 계정이 있는지 검사합니다.
        $hasSync = DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();

        if (!$hasSync) {
            return redirect('/partner/account-setting/dowewing-integration/');
        }

        set_time_limit(0);

        // 활성화된 오픈마켓 목록을 가져옵니다.
        $openMarkets = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('type', 'OPEN_MARKET')
            ->get();

        // 선택된 오픈마켓 ID와 해당 정보를 가져옵니다.
        $selectedOpenMarketId = $request->input('selectedOpenMarketId', 51);
        $selectedOpenMarket = DB::table('vendors')
            ->where('id', $selectedOpenMarketId)
            ->first();

        $vendorEngName = $selectedOpenMarket->name_eng;

        // 마진율을 가져옵니다.
        $margin = DB::table('sellwing_config')
            ->where('id', 1)
            ->first(['value'])
            ->value;

        $marginRate = $margin / 100 + 1;

        // 검색 키워드와 상품 코드를 입력받습니다.
        $searchKeyword = $request->input('searchKeyword', '');
        $searchProductCodes = $request->input('searchProductCodes', '');

        // 상품 코드 배열을 생성합니다.
        $productCodesArr = [];
        if (!empty($searchProductCodes)) {
            $productCodesArr = array_map('trim', explode(',', $searchProductCodes));
        }

        // 업로드된 상품을 검색합니다.
        $uploadedProducts = DB::table($vendorEngName . '_uploaded_products AS up')
            ->join('minewing_products AS mp', 'mp.id', '=', 'up.product_id')
            ->join($vendorEngName . '_accounts AS va', 'va.id', '=', 'up.' . $vendorEngName . '_account_id')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
            ->join('partners AS p', 'p.id', '=', 'va.partner_id')
            ->where('up.is_active', 'Y')
            ->where('va.partner_id', $partnerId)
            ->when(!empty($searchKeyword), function ($query) use ($searchKeyword) {
                $query->where(function ($query) use ($searchKeyword) {
                    $query->where('mp.productName', 'LIKE', "%{$searchKeyword}%")
                        ->orWhere('mp.productKeywords', 'LIKE', "%{$searchKeyword}%")
                        ->orWhere('oc.name', 'LIKE', "%{$searchKeyword}%")
                        ->orWhere('mp.productCode', 'LIKE', "%{$searchKeyword}%")
                        ->orWhere('up.origin_product_no', 'LIKE', "%{$searchKeyword}%");
                });
            })
            ->when(!empty($productCodesArr), function ($query) use ($productCodesArr) {
                $query->whereIn('mp.productCode', $productCodesArr);
            })
            ->orderByDesc('up.created_at')
            ->select([
                'mp.productCode',
                'up.product_name AS upName',
                'mp.productName AS mpName',
                'mp.productImage',
                'up.price',
                DB::raw("CEIL((mp.productPrice * $marginRate)) AS productPrice"),
                'mp.shipping_fee AS mp_shipping_fee',
                'oc.name',
                'up.shipping_fee AS up_shipping_fee',
                'up.origin_product_no',
                'va.username',
                'up.created_at',
                'up.origin_product_no',
                'mp.createdAt AS mca'
            ])
            ->paginate(500);

        return view('partner.products_uploaded', [
            'openMarkets' => $openMarkets,
            'uploadedProducts' => $uploadedProducts,
            'selectedOpenMarketId' => $selectedOpenMarketId,
            'searchKeyword' => $searchKeyword,
            'searchProductCodes' => $searchProductCodes
        ]);
    }
    public function delete(Request $request)
    {
        set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'originProductsNo' => 'required|array|min:1',
            'vendorId' => 'required|integer|exists:vendors,id'
        ], [
            'originProductNo' => '유효한 상품이 아닙니다.',
            'vendorId' => '유효한 오픈 마켓을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ];
        }
        $vendorId = $request->vendorId;
        $vendorEngName = DB::table('vendors')
            ->where('id', $vendorId)
            ->value('name_eng');
        $originProductsNo = DB::table($vendorEngName . '_uploaded_products')
            ->where('is_active', 'Y')
            ->whereIn('origin_product_no', $request->originProductsNo)
            ->pluck('origin_product_no');
        $errors = [];
        $successedOriginProductsNo = [];
        foreach ($originProductsNo as $originProductNo) {
            $functionName = $vendorEngName . 'DeleteRequest';
            $result = $this->$functionName($originProductNo, $vendorEngName);
            if ($result['status'] === false) {
                $error = $result['error'];
                $errors = [
                    'originProductNo' => $originProductNo,
                    'error' => $error
                ];
            } else {
                $successedOriginProductsNo[] = $originProductNo;
            }
        }
        $dupResult = $this->destroyUploadedProducts($successedOriginProductsNo, $vendorEngName);
        return [
            'status' => true,
            'message' => '총 ' . count($originProductsNo) . '개의 상품들 중 ' . count($successedOriginProductsNo) . '개의 상품들을 성공적으로 삭제했습니다.',
            'data' => [
                'success' => $successedOriginProductsNo,
                'errors' => $errors,
                'dupResult' => $dupResult
            ]
        ];
    }
    public function destroyUploadedProducts($originProductsNo, $vendorEngName)
    {
        try {
            DB::table($vendorEngName . '_uploaded_products')
                ->whereIn('origin_product_no', $originProductsNo)
                ->update(['is_active' => 'N']);
            return [
                'status' => true,
                'message' => '상품셋을 성공적으로 삭제했습니다.',
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품셋을 삭제처리 하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }

    public function edit(Request $request)
    {
        set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'originProductNo' => 'required',
            'productName' => 'required|string',
            'price' => 'required|integer',
            'shippingFee' => 'required|integer',
            'vendorId' => 'required|integer|exists:vendors,id'
        ], [
            'originProductNo' => '유효한 상품이 아닙니다.',
            'productName' => '상품명을 입력해주세요.',
            'price' => '상품가는 최소 10원부터 최대 999,999,999원까지 가능합니다.',
            'shippingFee' => '배송비는 최소 10원부터 최대 999,999,999원까지 가능합니다.',
            'vendorId' => '유효한 오픈 마켓이 아닙니다.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first(),
                'error' => $validator->errors()
            ];
        }
        $vendorId = $request->vendorId;
        $vendor = DB::table('vendors')
            ->where('id', $vendorId)
            ->select('name_eng')
            ->first();
        $vendorEngName = $vendor->name_eng;
        $originProductNoValidator = DB::table($vendorEngName . '_uploaded_products')
            ->where('origin_product_no', $request->originProductNo)
            ->where('is_active', 'Y')
            ->exists();
        if (!$originProductNoValidator) {
            return [
                'status' => false,
                'message' => '유효한 상품이 아닙니다.',
                'error' => [
                    'originProductNo' => $vendorEngName
                ]
            ];
        }
        $apiToken = $request->apiToken;
        $partner = DB::table('partners')
            ->where('api_token', $apiToken)
            ->first();
        $originProductNo = $request->originProductNo;
        $nc = new NameController();
        $productName = $nc->index($request->productName);
        $price = $request->price;
        $shippingFee = $request->shippingFee;
        $product = DB::table('minewing_products AS mp')
            ->join($vendorEngName . '_uploaded_products AS up', 'up.product_id', '=', 'mp.id')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
            ->where('up.origin_product_no', $originProductNo)
            ->first();
        if (!$product) {
            return [
                'status' => false,
                'message' => 'Product not found.'
            ];
        }
        // 벤더에 따라 올바른 메소드를 호출하도록 분기
        $methodName = $vendorEngName . 'EditRequest';
        $response = $this->$methodName($originProductNo, $productName, $price, $shippingFee, $partner, $product);
        if ($response['status'] === false) {
            return $response;
        }
        return $this->editUploadedProducts($vendorEngName, $originProductNo, $productName, $price, $shippingFee);
    }

    protected function editUploadedProducts($vendorEngName, $originProductNo, $productName, $price, $shippingFee)
    {
        try {
            DB::table($vendorEngName . '_uploaded_products')
                ->where('origin_product_no', $originProductNo)
                ->update([
                    'product_name' => $productName,
                    'price' => $price,
                    'shipping_fee' => $shippingFee
                ]);
            return [
                'status' => true,
                'message' => '상품 정보를 성공적으로 수정 및 반영했습니다.',
                'data' => [
                    'originProductNo' => $originProductNo
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품 정보를 수정하는 과정에서 오류가 발생했습니다.',
                'data' => $e->getMessage()
            ];
        }
    }

    public function coupangEditRequest($originProductNo, $productName, $price, $shippingFee, $partner, $product)
    {
        $cpac = new ApiController();
        $account = DB::table('coupang_accounts AS a')
            ->join('coupang_uploaded_products AS up', 'up.coupang_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.hash', 'a.secret_key', 'a.access_key', 'a.code'])
            ->first();
        $cuc = new CoupangUploadController($product, $partner, $account);
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $responseOutbound = $cuc->getOutbound($accessKey, $secretKey);
        $responseReturn = $cuc->getReturnCenter($accessKey, $secretKey, $account->code);
        if ($responseOutbound['status'] === false) {
            return $responseOutbound;
        }
        if ($responseReturn['status'] === false) {
            return $responseReturn;
        }
        $outboundCode = $responseOutbound['data'];
        $returnCenter = $responseReturn['data'];
        return [
            'status' => true,
            'data' => $returnCenter
        ];
        //ㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡ
        $optionName = '단일 상품';
        if ($product->hasOption === 'Y') {
            $optionName = $this->extractOptionName($product->productDetail);
        }
        $deliveryChargeType = "NOT_FREE";
        if ($price >= 5000) { //애매한부분 %salePrice
            $deliveryChargeType = "FREE";
        }
        $deliveryCharge = $shippingFee;
        //ㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡ
        $data = [
            'sellerProductId' => $originProductNo,
            'displayCategoryCode' => $product->code,
            'sellerProductName' => $productName,
            'saleStartedAt' => date("Y-m-d\TH:i:s"),
            'saleEndedAt' => date("2099-12-31\TH:i:s"),
            'displayProductName' => $productName,
            'brand' => '제이에스',
            'generalProductName' => $productName,
            'deliveryMethod' => 'SEQUENCIAL',
            'deliveryCompanyCode' => 'HYUNDAI',
            'deliveryChargeType' => $deliveryChargeType,
            'deliveryCharge' => $deliveryCharge,
            'freeShipOverAmount' => 0,
            'deliveryChargeOnReturn' => $shippingFee,
            'remoteAreaDeliverable' => 'Y',
            'unionDeliveryType' => 'NOT_UNION_DELIVERY',
            'returnCenterCode' => "NO_RETURN_CENTERCODE",
            'returnChargeName' => $returnCenter['shippingPlaceName'],
            'companyContactNumber' => $returnCenter['placeAddresses'][0]['companyContactNumber'],
            'returnZipCode' => $returnCenter['placeAddresses'][0]['returnZipCode'],
            'returnAddress' => $returnCenter['placeAddresses'][0]['returnAddress'],
            'returnAddressDetail' => $returnCenter['placeAddresses'][0]['returnAddressDetail'],
            'returnCharge' => $shippingFee,
            'outboundShippingPlaceCode' => $outboundCode,
            'vendorUserId' => $account->username,
            'requested' => true,
            'items' => [
                [
                    'itemName' => $optionName,
                    'originalPrice' => $price,
                    'salePrice' => $price,
                    'maximumBuyCount' => 9999,
                    'maximumBuyForPerson' => 0,
                    'maximumBuyForPersonPeriod' => 1,
                    'outboundShippingTimeDay' => 1,
                    'unitCount' => 0,
                    'adultOnly' => 'EVERYONE',
                    'taxType' => 'TAX',
                    'parallelImported' => 'NOT_PARALLEL_IMPORTED',
                    'overseasPurchased' => 'NOT_OVERSEAS_PURCHASED',
                    'pccNeeded' => false,
                    'images' => [
                        [
                            'imageOrder' => 0,
                            'imageType' => 'REPRESENTATION',
                            'vendorPath' => $product->productImage
                        ]
                    ],
                    'notices' => [
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '품명 및 모델명',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '인증/허가 사항',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '제조국(원산지)',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '제조자(수입자)',
                            'content' => '상세페이지 참조'
                        ],
                        [
                            'noticeCategoryName' => '기타 재화',
                            'noticeCategoryDetailName' => '소비자상담 관련 전화번호',
                            'content' => '상세페이지 참조'
                        ],
                    ],
                    'contents' => [
                        [
                            'contentsType' => 'HTML',
                            'contentDetails' => [
                                [
                                    'content' => $product->productDetail,
                                    'detailType' => 'TEXT'
                                ]
                            ]
                        ]
                    ],
                    'attributes' => []
                ]
            ]
        ];
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products';
        $apiResult = $cpac->putBuilder($account->access_key, $account->secret_key, $contentType, $path, $data);
        return $apiResult;
    }

    public function coupangDeleteRequest($originProductNo)
    {
        $cpac = new ApiController();
        $account = DB::table('coupang_accounts AS a')
            ->join('coupang_uploaded_products AS up', 'up.coupang_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.hash', 'a.secret_key', 'a.access_key'])
            ->first();
        $contentType = 'application/json;charset=UTF-8';
        $path = "/v2/providers/seller_api/apis/api/v1/marketplace/seller-products/" . $originProductNo;
        $apiResult = $cpac->getBuilder($account->access_key, $account->secret_key, $contentType, $path);
        if (isset($apiResult['error'])) {
            return [
                'status' => false,
                'message' => '상품 정보를 불러오는 과정에서 오류가 발생했습니다.',
                'error' => $apiResult['error']
            ];
        }
        $vendorItemId = $apiResult['data']['data']['items'][0]['vendorItemId'];
        $url = '/v2/providers/seller_api/apis/api/v1/marketplace/vendor-items/' . $vendorItemId . '/sales/stop';
        return $cpac->putBuilder($account->access_key, $account->secret_key, $contentType, $url);
    }

    public function smart_storeEditRequest($originProductNo, $productName, $price, $shippingFee, $partner, $product)
    {
        $ssac = new SmartStoreApiController();
        $account = DB::table('smart_store_accounts AS a')
            ->join('smart_store_uploaded_products AS up', 'up.smart_store_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.application_id', 'a.secret', 'a.username'])
            ->first();
        $spu = new SmartstoreProductUpload([$product], $partner, $account);
        $contentType = 'application/json;charset=UTF-8';
        $method = "put";
        $url = 'https://api.commerce.naver.com/external/v2/products/origin-products/' . $originProductNo;
        $uploadImageResult = $spu->uploadImageFromUrl($product->productImage, $account);
        if ($uploadImageResult['status'] === false) {
            return $uploadImageResult;
        }
        $productImage = $uploadImageResult['data']['images'][0]['url'];
        $data = [
            'originProduct' => [
                'statusType' => 'SALE',
                'name' => $productName,
                'detailContent' => $product->productDetail,
                'images' => [
                    'representativeImage' => [
                        'url' => $productImage
                    ]
                ],
                'salePrice' => $price * 2,
                'stockQuantity' => 9999,
                'deliveryInfo' => [
                    'deliveryType' => 'DELIVERY',
                    'deliveryAttributeType' => 'NORMAL',
                    'deliveryCompany' => 'HYUNDAI',
                    'deliveryBundleGroupUsable' => false,
                    'deliveryFee' => [
                        'deliveryFeeType' => 'PAID',
                        'deliveryFeePayType' => 'PREPAID',
                        'baseFee' => $shippingFee,
                    ],
                    'claimDeliveryInfo' => [
                        'returnDeliveryFee' => (int)$shippingFee,
                        'exchangeDeliveryFee' => (int)$shippingFee * 2
                    ],
                    'installationFee' => false,
                ],
                'detailAttribute' => [
                    'afterServiceInfo' => [
                        'afterServiceTelephoneNumber' => (string)$partner->phone,
                        'afterServiceGuideContent' => '평일 09:00 ~ 17:00까지 응대가 가능하며, 주말 및 공휴일은 쉽니다.'
                    ],
                    'originAreaInfo' => [
                        'originAreaCode' => '03'
                    ],
                    'sellerCodeInfo' => [
                        'sellerManagementCode' => $product->productCode
                    ],
                    'taxType' => 'TAX',
                    'minorPurchasable' => true,
                    'certificationTargetExcludeContent' => [
                        'childCertifiedProductExclusionYn' => true,
                        'kcCertifiedProductExclusionYn' => "TRUE",
                        'greenCertifiedProductExclusionYn' => true
                    ],
                    'productInfoProvidedNotice' => [
                        'productInfoProvidedNoticeType' => 'ETC',
                        'etc' => [
                            'returnCostReason' => '상품상세 참조',
                            'noRefundReason' => '상품상세 참조',
                            'qualityAssuranceStandard' => '상품상세 참조',
                            'compensationProcedure' => '상품상세 참조',
                            'troubleShootingContents' => '상품상세 참조',
                            'itemName' => $productName,
                            'modelName' => '제이에스',
                            'manufacturer' => '제이에스',
                            'afterServiceDirector' => '제이에스',
                        ]
                    ]
                ],
                'customerBenefit' => [
                    'immediateDiscountPolicy' => [
                        'discountMethod' => [
                            'value' => 50,
                            'unitType' => 'PERCENT'
                        ]
                    ],
                    'reviewPolicy' => [
                        'textReviewPoint' => 100,
                        'photoVideoReviewPoint' => 150,
                        'afterUseTextReviewPoint' => 100,
                        'afterUsePhotoVideoReviewPoint' => 150
                    ],
                    'giftPolicy' => [
                        'presentContent' => '리뷰 이벤트'
                    ],
                    'multiPurchaseDiscountPolicy' => [
                        'discountMethod' => [
                            'value' => 1,
                            'unitType' => 'PERCENT'
                        ],
                        'orderValue' => '5',
                        'orderValueUnitType' => 'COUNT'
                    ]
                ]
            ],
            'smartstoreChannelProduct' => [
                'channelProductName' => $productName,
                'naverShoppingRegistration' => true,
                'channelProductDisplayStatusType' => 'ON'
            ]
        ];
        return $ssac->builder($account, $contentType, $method, $url, $data);
    }

    public function smart_storeDeleteRequest($originProductNo)
    {
        $ssac = new SmartStoreApiController();
        $account = DB::table('smart_store_accounts AS a')
            ->join('smart_store_uploaded_products AS up', 'up.smart_store_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.application_id', 'a.secret', 'a.username'])
            ->first();
        $contentType = 'application/json;charset=UTF-8';
        $method = "delete";
        $url = 'https://api.commerce.naver.com/external/v2/products/origin-products/' . $originProductNo;
        $data =  "";
        return $ssac->builder($account, $contentType, $method, $url, $data);
    }

    public function destroy($originProductsNo)
    {
        try {
            DB::table('partner_products AS pp')
                ->join('minewing_products AS mp', 'mp.id', '=', 'pp.product_id')
                ->whereIn('mp.productCode', $originProductsNo)
                ->delete();
            return [
                'status' => true,
                'message' => '선택된 상품들이 성공적으로 삭제되었습니다.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => '상품을 삭제하는 과정에서 오류가 발생했습니다.',
                'error' => $e->getMessage()
            ];
        }
    }
    protected function st11EditRequest($originProductNo, $productName, $price, $shippingFee, $partner, $product)
    {
    }
}
