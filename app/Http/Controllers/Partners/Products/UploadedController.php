<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangUploadController;
use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnApiController;
use App\Http\Controllers\OpenMarkets\St11\ApiController as St11ApiController;
use App\Http\Controllers\OpenMarkets\St11\UploadController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use App\Http\Controllers\Product\NameController;
use App\Http\Controllers\SmartStore\SmartstoreProductUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

use function PHPSTORM_META\map;

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

        $accounts = DB::table($vendorEngName . '_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'ACTIVE')
            ->get(['hash', 'username']);
        $accountHashes = $request->input('accountHashes', []);

        // 업로드된 상품을 검색합니다.
        $uploadedProducts = DB::table($vendorEngName . '_uploaded_products AS up')
            ->join('minewing_products AS mp', 'mp.id', '=', 'up.product_id')
            ->join($vendorEngName . '_accounts AS va', 'va.id', '=', 'up.' . $vendorEngName . '_account_id')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
            ->join('partners AS p', 'p.id', '=', 'va.partner_id')
            ->join($vendorEngName . '_accounts AS a', 'a.id', '=', 'up.' . $vendorEngName . '_account_id')
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
            ->when(!empty($accountHashes), function ($query) use ($accountHashes) {
                $query->whereIn('a.hash', $accountHashes);
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
            'searchProductCodes' => $searchProductCodes,
            'accounts' => $accounts,
            'accountHashes' => $accountHashes
        ]);
    }
    public function delete(Request $request)
    {
        set_time_limit(0);

        // Request validation
        $validator = Validator::make($request->all(), [
            'originProductsNo' => 'required|array|min:1',
            'vendorId' => 'required|integer|exists:vendors,id'
        ], [
            'originProductsNo.required' => '유효한 상품이 아닙니다.',
            'vendorId.required' => '유효한 오픈 마켓을 선택해주세요.'
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

        $originProductsNo = DB::table("{$vendorEngName}_uploaded_products")
            ->whereIn('origin_product_no', $request->originProductsNo)
            ->pluck('origin_product_no');

        $errors = [];
        $successedOriginProductsNo = [];

        foreach ($originProductsNo as $originProductNo) {
            $functionName = "{$vendorEngName}DeleteRequest";
            $result = $this->$functionName($originProductNo, $vendorEngName);

            if ($result['status'] === false) {
                $errors[] = [
                    'originProductNo' => $originProductNo,
                    'error' => $result['error'] ?? 'Unknown error'
                ];
            } else {
                $successedOriginProductsNo[] = $originProductNo;
            }
        }

        $dupResult = $this->destroyUploadedProducts($originProductsNo, $vendorEngName);

        return [
            'status' => true,
            'message' => '총 ' . count($originProductsNo) . '개의 상품 중 ' . count($successedOriginProductsNo) . '개의 상품을 성공적으로 삭제했습니다.<br>주문 및 클레임 진행 중인 상품들은 삭제할 수 없습니다. 해당 상품들은 주문 및 클레임이 완료된 후, 판매자 센터에서 수동으로 삭제해주시기 바랍니다.',
            'data' => [
                'success' => $successedOriginProductsNo,
                'errors' => $errors,
                'dupResult' => $dupResult,
                'apiResult' => $result
            ]
        ];
    }
    public function destroyUploadedProducts($originProductsNo, $vendorEngName)
    {
        set_time_limit(0);
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
            ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
            ->join($vendorEngName . '_accounts AS a', 'a.id', '=', 'up.' . $vendorEngName . '_account_id')
            ->where('up.origin_product_no', $originProductNo)
            ->first();
        if (!$product) {
            return [
                'status' => false,
                'message' => '유효한 상품이 아닙니다.'
            ];
        }
        // 벤더에 따라 올바른 메소드를 호출하도록 분기
        $methodName = $vendorEngName . 'EditRequest';
        $response = $this->$methodName($originProductNo, $productName, $price, $shippingFee, $partner, $product, $request->bundleQuantity);
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
    protected function coupangGetProduct($accessKey, $secretKey, $originProductNo)
    {
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products/' . $originProductNo;
        $ac = new ApiController();
        $apiResult = $ac->getBuilder($accessKey, $secretKey, $contentType, $path);
        return $apiResult;
    }
    public function coupangEditRequest($originProductNo, $productName, $price, $shippingFee, $partner, $product)
    {
        $account = DB::table('coupang_accounts AS a')
            ->join('coupang_uploaded_products AS up', 'up.coupang_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.access_key', 'a.secret_key', 'a.code', 'a.username'])
            ->first();
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products';
        $coupangGetProductResult = $this->coupangGetProduct($accessKey, $secretKey, $originProductNo);
        if ($coupangGetProductResult['status'] === false || $coupangGetProductResult['data']['code'] !== 'SUCCESS') {
            return $coupangGetProductResult;
        }
        $productInfo = $coupangGetProductResult['data']['data'];
        $deliveryChargeType = $shippingFee > 0 ? 'NOT_FREE' : 'FREE';
        $returnCharge = ($shippingFee > 0)
            ? $shippingFee
            : (($product->shipping_fee > 0)
                ? $product->shipping_fee
                : 3000);
        $productInfo['items'][0]['originalPrice'] = $price;
        $productInfo['items'][0]['salePrice'] = $price;
        $productInfo['displayProductName'] = $productName;
        $productInfo['generalProductName'] = $productName;
        $productInfo['deliveryChargeType'] = $deliveryChargeType;
        $productInfo['deliveryCharge'] = $shippingFee;
        $productInfo['deliveryChargeOnReturn'] = $returnCharge;
        $productInfo['returnCharge'] = $returnCharge;
        $productInfo['sellerProductName'] = $productName;
        $ac = new ApiController();
        $apiResult = $ac->putBuilder($accessKey, $secretKey, $contentType, $path, $productInfo);
        if ($apiResult['status'] === false || $apiResult['data']['code'] !== 'SUCCESS') {
            return $apiResult;
        }
        return [
            'status' => true,
            'data' => $productInfo
        ];
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

    public function smart_storeEditRequest($originProductNo, $productName, $price, $shippingFee, $partner, $product, $bundleQuantity)
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
        $deliveryFeeType = 'PAID';
        if ($bundleQuantity > 0) {
            $deliveryFeeType = 'UNIT_QUANTITY_PAID';
        }
        $data = [
            'originProduct' => [
                'statusType' => 'SALE',
                'leafCategoryId' => $product->code,
                'name' => $productName,
                'detailContent' => '<center><img src="https://www.sellwing.kr/images/CDN/ss_header.jpg"><br></center>' . $product->productDetail,
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
                        'deliveryFeeType' => $deliveryFeeType,
                        'baseFee' => $shippingFee,
                        'repeatQuantity' => $bundleQuantity,
                        'deliveryFeePayType' => 'PREPAID',
                        'deliveryFeeByArea' => [
                            'deliveryAreaType' => 'AREA_3',
                            'area3extraFee' => $product->additional_shipping_fee,
                            'area2extraFee' => $product->additional_shipping_fee
                        ]
                    ],
                    'claimDeliveryInfo' => [
                        'returnDeliveryFee' => (int)$shippingFee,
                        'exchangeDeliveryFee' => (int)$shippingFee * 2
                    ],
                    'installationFee' => false,
                ],
                'detailAttribute' => [
                    'afterServiceInfo' => [
                        'afterServiceTelephoneNumber' => $partner->phone,
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
    public function st11EditRequest($originProductNo, $productName, $price, $shippingFee, $partner, $product)
    {
        $ac = new St11ApiController();
        $account = DB::table('st11_accounts AS a')
            ->join('st11_uploaded_products AS up', 'up.st11_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.hash', 'a.access_key'])
            ->first();
        $apiKey = $account->access_key;
        $uc = new UploadController();
        $getOutboundCodeResult = $uc->getOutboundCode($apiKey);
        $getInboundCodeResult = $uc->getInboundCode($apiKey);
        if ($getOutboundCodeResult['status'] === false) {
            return $getOutboundCodeResult;
        }
        if ($getInboundCodeResult['status'] === false) {
            return $getInboundCodeResult;
        }
        $outboundCode = $getOutboundCodeResult['data']['addrSeq'];
        $inboundCode = $getInboundCodeResult['data']['addrSeq'];
        $data = <<<_EOT_
        <?xml version="1.0" encoding="euc-kr" ?>
        <Product>
            <selMthdCd>01</selMthdCd>
            <prdTypCd>01</prdTypCd>
            <prdNm>$productName</prdNm>
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
            <selPrc>$price</selPrc>
            <dlvCnAreaCd>01</dlvCnAreaCd>
            <dlvWyCd>01</dlvWyCd>
            <dlvCstInstBasiCd>03</dlvCstInstBasiCd>
            <PrdFrDlvBasiAmt>300000</PrdFrDlvBasiAmt>
            <bndlDlvCnYn>N</bndlDlvCnYn>
            <dlvCstPayTypCd>03</dlvCstPayTypCd>
            <jejuDlvCst>$product->additional_shipping_fee</jejuDlvCst>
            <islandDlvCst>$product->additional_shipping_fee</islandDlvCst>
            <addrSeqOut>$outboundCode</addrSeqOut>
            <addrSeqIn>$inboundCode</addrSeqIn>
            <rtngdDlvCst>$shippingFee</rtngdDlvCst>
            <exchDlvCst>$shippingFee</exchDlvCst>
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
            <dlvCst1>$shippingFee</dlvCst1>
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
        $url = 'http://api.11st.co.kr/rest/prodservices/product/' . $originProductNo;
        $method = 'put';
        $apiResult = $ac->builder($apiKey, $method, $url, $data);
        if ($apiResult['status'] === true) {
            $resultCode = (int)$apiResult['data']->resultCode;
            if ($resultCode === 200) {
                return [
                    'status' => true
                ];
            }
            return [
                'status' => false,
                'apiResult' => $apiResult
            ];
        }
        return $apiResult;
    }
    protected function st11DeleteRequest($originProductNo)
    {
        $ac = new St11ApiController();
        $account = DB::table('st11_accounts AS a')
            ->join('st11_uploaded_products AS up', 'up.st11_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.hash', 'a.access_key'])
            ->first();
        $apiKey = $account->access_key;
        $method = 'put';
        $url = "http://api.11st.co.kr/rest/prodstatservice/stat/stopdisplay/" . $originProductNo;
        $apiResult = $ac->builder($apiKey, $method, $url);
        if ($apiResult['status'] === true) {
            $resultCode = (int)$apiResult['data']->resultCode;
            if ($resultCode === 200) {
                return [
                    'status' => true
                ];
            }
            return [
                'status' => false,
                'apiResult' => $apiResult
            ];
        }
        return $apiResult;
    }
    /**
     * 롯데온 상품을 삭제 요청합니다.
     *
     * @param string @originProductNo
     * @return array
     */
    public function lotte_onDeleteRequest(string $originProductNo): array
    {
        $loac = new LotteOnApiController();
        $account = DB::table('lotte_on_accounts AS a')
            ->join('lotte_on_uploaded_products AS up', 'up.lotte_on_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.partner_code', 'a.access_key'])
            ->first();
        $method = 'post';
        $url = "https://openapi.lotteon.com/v1/openapi/product/v1/product/status/change";
        $data['spdLst'] = [
            [
                'trGrpCd' => 'SR',
                'trNo' => $account->partner_code,
                'spdNo' => $originProductNo,
                'slStatCd' => 'END'
            ]
        ];
        $apiResult = $loac->builder($method, $account->access_key, $url, $data);
        if (!isset($apiResult['data']['data'][0]['resultCode']) || (int)$apiResult['data']['data'][0]['resultCode'] !== 0000) {
            return [
                'status' => false,
                'apiResult' => $apiResult
            ];
        }
        return [
            'status' => true
        ];
    }

    /**
     * 롯데온에 업로드된 상품 정보를 수정합니다.
     *
     * @param string $originProductNo
     * @param string $productName
     * @param int $price
     * @param int $shippingFee
     * @param stdClass $partner
     * @param stdClass $product
     * @return array
     */
    public function lotte_onEditRequest(string $originProductNo, string $productName, int $price, int $shippingFee, stdClass $partner, stdClass $product, $bundleQuantity): array
    {
        $account = DB::table('lotte_on_accounts AS a')
            ->join('lotte_on_uploaded_products AS up', 'up.lotte_on_account_id', '=', 'a.id')
            ->where('up.origin_product_no', $originProductNo)
            ->select(['a.partner_code', 'a.access_key'])
            ->first();
        $prdByMaxPurPsbQtyYn = $bundleQuantity > 0 ? 'Y' : 'N';
        $loac = new LotteOnApiController();
        $url = 'https://openapi.lotteon.com/v1/openapi/product/v1/product/detail';
        $data = [
            'trGrpCd' => 'SR',
            'trNo' => $account->partner_code,
            'spdNo' => $originProductNo
        ];
        $builderResult = $loac->builder('post', $account->access_key, $url, $data);
        if (!$builderResult['status']) {
            return $builderResult;
        }
        $builderData = $builderResult['data'];
        if (!isset($builderData['data']['itmLst'][0]['sitmNo'])) {
            return [
                'status' => false,
                'error' => $builderData
            ];
        }
        $sitmNo = $builderData['data']['itmLst'][0]['sitmNo'];
        $data['spdLst'][] = [
            'trGrpCd' => 'SR',
            'trNo' => $account->partner_code,
            'epdNo' => $product->productCode,
            'spdNo' => $product->origin_product_no,
            'slTypCd' => 'GNRL',
            'pdTypCd' => 'GNRL_GNRL',
            'spdNm' => $productName,
            'oplcCd' => '상품상세 참조',
            'tdfDvsCd' => '01',
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
                'maxPurQty' => $bundleQuantity,
                'maxPurLmtTypCd' => 'ONCE'
            ],
            'ageLmtCd' => '0',
            'prstPckPsbYn' => 'N',
            'prstMsgPsbYn' => 'N',
            'pdStatCd' => 'NEW',
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
            'stkMgtYn' => 'N',
            'sitmYn' => 'Y',
            'itmLst' => [
                [
                    'eitmNo' => $product->productCode,
                    'sitmNo' => $sitmNo,
                    'dpYn' => 'Y',
                    "sortSeq" => 1,
                    'itmImgLst' => [
                        [
                            'epsrTypCd' => 'IMG',
                            'epsrTypDtlCd' => 'IMG_SQRE',
                            'origImgFileNm' => $product->productImage,
                            'rprtImgYn' => 'Y'
                        ]
                    ],
                    'slPrc' => round($price, -1)
                ],
            ],
        ];
        $method = 'post';
        $url = 'https://openapi.lotteon.com/v1/openapi/product/v1/product/modification/request';
        $builderResult = $loac->builder($method, $account->access_key, $url, $data);
        if (!isset($builderResult['data']['data'][0]['resultCode']) || (int)$builderResult['data']['data'][0]['resultCode'] !== 0000) {
            return [
                'status' => false,
                'apiResult' => $builderResult
            ];
        }
        return [
            'status' => true
        ];
    }

    public function fetchEdittedProducts(array $products)
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        // 활성화된 'OPEN_MARKET' 타입의 모든 벤더를 조회하여 $openMarkets에 저장
        $openMarkets = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('type', 'OPEN_MARKET')
            ->get(['name_eng', 'id']);

        // 에러를 저장할 배열 초기화
        $errors = [];

        // 입력받은 제품 목록을 하나씩 처리
        foreach ($products as $product) {
            // 입력받은 제품의 제품 코드를 기준으로 기존 제품 정보 조회
            $oldProduct = DB::table('minewing_products')
                ->where('productCode', $product['productCode'])
                ->first(['id', 'productPrice', 'shipping_fee']);

            // 모든 오픈 마켓 벤더를 순회
            foreach ($openMarkets as $openMarket) {
                // 각 벤더의 업로드된 제품 중 해당 제품의 ID와 일치하는 제품 목록 조회
                $uploadedProducts = DB::table($openMarket->name_eng . '_uploaded_products AS up')
                    ->join($openMarket->name_eng . '_accounts AS a', 'a.id', '=', 'up.' . $openMarket->name_eng . '_account_id')
                    ->join('partners AS p', 'p.id', '=', 'a.partner_id')
                    ->where('up.product_id', $oldProduct->id)
                    ->get(['up.origin_product_no', 'up.price', 'p.api_token']);

                // 조회된 업로드된 제품 목록을 순회
                foreach ($uploadedProducts as $uploadedProduct) {
                    if ($openMarket->id === 40) {
                        $vendorCommission = DB::table('vendor_commissions')
                            ->where('vendor_id', 40)
                            ->value(DB::raw('(commission / 100) + 1'));
                        $marginRate = 1.1;
                        $newPrice = round($product['productPrice'] * $marginRate * $vendorCommission, -1);
                        $shippingFee = $product['shipping_fee'];
                        if ($newPrice >= 5000) {
                            $newPrice = $newPrice + $product['shipping_fee'];
                            $shippingFee = 0;
                        }
                    } else {
                        $marginRate = $uploadedProduct->price / $oldProduct->productPrice;
                        $newPrice = round($product['productPrice'] * $marginRate, -1);
                        $shippingFee = $product['shipping_fee'];
                    }
                    // 제품 수정 요청을 위한 데이터 생성
                    $editRequest = new Request([
                        'originProductNo' => $uploadedProduct->origin_product_no,
                        'productName' => $product['productName'],
                        'price' => $newPrice,
                        'shippingFee' => $shippingFee,
                        'vendorId' => $openMarket->id,
                        'apiToken' => $uploadedProduct->api_token,
                        'bundleQuantity' => $product['bundle_quantity']
                    ]);

                    // 제품 수정 요청 실행
                    $editResult = $this->edit($editRequest);

                    // 수정 요청이 실패하면 에러 배열에 추가
                    if (!$editResult['status']) {
                        $errors[] = $editResult;
                    }
                }
            }
        }
        return $errors;
    }
}
