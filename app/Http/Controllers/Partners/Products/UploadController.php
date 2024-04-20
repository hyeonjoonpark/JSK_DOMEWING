<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreAccountController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $openMarkets = DB::table('vendors')
            ->where('is_active', 'ACTIVE')
            ->where('type', 'OPEN_MARKET')
            ->get();
        $partnerTables = DB::table('partner_tables')
            ->where("is_active", 'Y')
            ->where('partner_id', Auth::guard('partner')->id())
            ->get();
        return view('partner.products_upload', [
            'openMarkets' => $openMarkets,
            'partnerTables' => $partnerTables
        ]);
    }
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partnerTableToken' => 'required|string',
            'vendorId' => 'required'
        ], [
            'partnerTableToken' => '상품 업로드를 위한 상품 테이블을 생성해주세요.',
            'vendorId' => '상품 업로드를 위한 오픈 마켓을 선택해주세요.'
        ]);
        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }
        $partnerTableToken = $request->partnerTableToken;
        $partnerTableId = DB::table('partner_tables')
            ->where('token', $partnerTableToken)
            ->where('is_active', 'Y')
            ->first('id')
            ->id;
        $vendorId = $request->vendorId;
        $partnerId = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first(['id'])
            ->id;
        return $this->upload($partnerTableId, $vendorId, $partnerId);
    }
    private function upload($partnerTableId, $vendorId, $partnerId)
    {
        $vendorEngName = DB::table('vendors')
            ->where('id', $vendorId)
            ->first('name_eng')
            ->name_eng;
        $products = DB::table('partner_products AS pp')
            ->join('minewing_products AS mp', 'pp.product_id', '=', 'mp.id')
            ->join('category_mapping AS cm', 'mp.categoryID', '=', 'cm.ownerclan')
            ->join($vendorEngName . '_category AS vc', 'vc.id', '=', 'cm.' . $vendorEngName)
            ->join('product_search AS ps', 'mp.sellerID', '=', 'ps.vendor_id')
            ->where('mp.isActive', 'Y')
            ->where('pp.partner_table_id', $partnerTableId)
            ->get(['mp.productCode', 'mp.productName', 'mp.productPrice', 'mp.productImage', 'mp.productDetail', 'vc.code', 'ps.shipping_fee', 'ps.additional_shipping_fee']);
        return $this->$vendorEngName($products, $partnerId);
    }
    private function smart_store($products, $partnerId)
    {
        $partner = DB::table('partners')->where('id', $partnerId)->first();
        $margin = DB::table('product_register')
            ->where('id', 18)
            ->first(['margin_rate'])
            ->margin_rate;
        $marginRate = $margin / 100 + 1;
        $success = 0;
        foreach ($products as $product) {
            $productPrice = $product->productPrice;
            $salePrice = ceil($productPrice * $marginRate / 10) * 10;
            $productImage = $product->productImage;
            $productImageUrl = $this->uploadImageFromUrl($productImage, $partner->id);
            $productImageUrl = $productImageUrl['data']['images'][0]['url'];
            $data = [
                'originProduct' => [
                    'statusType' => 'SALE',
                    'leafCategoryId' => $product->code,
                    'name' => $product->productName,
                    'detailContent' => $product->productDetail,
                    'images' => [
                        'representativeImage' => [
                            'url' => $productImageUrl
                        ]
                    ],
                    'salePrice' => $salePrice,
                    'stockQuantity' => 9999,
                    'deliveryInfo' => [
                        'deliveryType' => 'DELIVERY',
                        'deliveryAttributeType' => 'NORMAL',
                        'deliveryCompany' => 'HYUNDAI',
                        'deliveryBundleGroupUsable' => false,
                        'deliveryFee' => [
                            'deliveryFeeType' => 'PAID',
                            'baseFee' => $product->shipping_fee,
                            'deliveryFeePayType' => 'PREPAID',
                            'deliveryFeeByArea' => [
                                'deliveryAreaType' => 'AREA_3',
                                'area3extraFee' => $product->additional_shipping_fee,
                                'area2extraFee' => $product->additional_shipping_fee
                            ]
                        ],
                        'claimDeliveryInfo' => [
                            'returnDeliveryFee' => (int)$product->shipping_fee,
                            'exchangeDeliveryFee' => (int)$product->shipping_fee * 2
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
                        'productInfoProvidedNotice' => [
                            'productInfoProvidedNoticeType' => 'ETC',
                            'etc' => [
                                'returnCostReason' => '상품상세 참조',
                                'noRefundReason' => '상품상세 참조',
                                'qualityAssuranceStandard' => '상품상세 참조',
                                'compensationProcedure' => '상품상세 참조',
                                'troubleShootingContents' => '상품상세 참조',
                                'itemName' => $product->productName,
                                'modelName' => '제이에스',
                                'manufacturer' => '제이에스',
                                'afterServiceDirector' => '제이에스',
                            ]
                        ]
                    ]
                ],
                'smartstoreChannelProduct' => [
                    'channelProductName' => $product->productName,
                    'naverShoppingRegistration' => true,
                    'channelProductDisplayStatusType' => 'ON'
                ]
            ];
            $ssac = new SmartStoreApiController();
            $result = $ssac->builder($partnerId, 'application/json', 'POST', 'https://api.commerce.naver.com/external/v2/products', $data);
            if ($result['status'] === true) {
                $success++;
            }
        }
        return [
            'status' => true,
            'message' => "총 " . count($products) . " 개의 상품들 중 $success 개의 상품을 성공적으로 업로드했습니다."
        ];
    }
    public function uploadImageFromUrl(string $imageUrl, int $partnerId): array
    {
        $imageContent = $this->downloadImage($imageUrl);
        if ($imageContent === false) {
            return ['status' => false, 'message' => 'Image download failed'];
        }

        $tempImagePath = $this->createTempImage($imageContent, $imageUrl);
        if ($tempImagePath === false) {
            return ['status' => false, 'message' => 'Failed to create temp file'];
        }

        $uploadResponse = $this->uploadToAPI($tempImagePath, $imageUrl, $partnerId);
        $this->cleanupTempFile($tempImagePath);

        return $uploadResponse;
    }
    protected function downloadImage(string $imageUrl)
    {
        try {
            $response = Http::get($imageUrl);
            return $response->successful() ? $response->body() : false;
        } catch (\Exception $e) {
            return false;
        }
    }
    protected function createTempImage(string $imageContent, string $imageUrl): string
    {
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $tempPath = tempnam(sys_get_temp_dir(), 'upload') . '.' . $extension;
        return file_put_contents($tempPath, $imageContent) ? $tempPath : false;
    }
    protected function uploadToAPI(string $tempImagePath, string $imageUrl, int $partnerId): array
    {
        // Ensure the file handle can be opened.
        $fileHandle = fopen($tempImagePath, 'r');
        if (!$fileHandle) {
            return ['status' => false, 'message' => 'Failed to open file handle'];
        }

        $multipartData = [
            [
                'name' => 'imageFiles',
                'contents' => $fileHandle,
                'filename' => basename($imageUrl)
            ]
        ];

        // Proceed with API upload.
        $response = $this->builder($partnerId, 'multipart/form-data', 'POST', 'https://api.commerce.naver.com/external/v1/product-images/upload', $multipartData);

        // Always close the file handle if it's valid.
        if (is_resource($fileHandle)) {
            fclose($fileHandle);
        }

        return $response;
    }
    protected function cleanupTempFile(string $tempImagePath): void
    {
        unlink($tempImagePath);
    }
    public function builder(int $partnerId, string $contentType, string $method, string $url, array $data): array
    {
        $account = DB::table('smart_store_accounts AS ssa')
            ->join('partners AS p', 'ssa.partner_id', '=', 'p.id')
            ->where('p.id', $partnerId)
            ->first();

        $ssac = new SmartStoreAccountController();
        $getAccessTokenResult = $ssac->getAccessToken($account->application_id, $account->secret, $account->username);

        if (!$getAccessTokenResult['status']) {
            return [
                'status' => false,
                'message' => '유효한 API 계정 정보가 아닙니다.',
                'error' => $getAccessTokenResult['message']
            ];
        }

        $accessToken = $getAccessTokenResult['data']->access_token;
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ];

        $client = new \GuzzleHttp\Client();
        $options = ['headers' => $headers];

        if ($contentType == 'multipart/form-data') {
            $options['multipart'] = $data;
        } else {
            $options['json'] = $data;
        }

        try {
            $response = $client->request($method, $url, $options);
            return [
                'status' => true,
                'data' => json_decode((string)$response->getBody(), true)
            ];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return [
                'status' => false,
                'message' => 'API 요청 실패',
                'error' => $e->getMessage()
            ];
        }
    }
}
