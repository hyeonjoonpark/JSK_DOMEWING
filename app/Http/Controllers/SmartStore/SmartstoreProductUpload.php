<?php

namespace App\Http\Controllers\SmartStore;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SmartstoreProductUpload extends Controller
{
    private $products, $partner, $account;
    public function __construct($products, $partner, $account)
    {
        $this->products = $products;
        $this->partner = $partner;
        $this->account = $account;
    }
    // 스마트 스토어 상품 업로드 프로세스 시작.
    public function main()
    {
        $success = 0;
        $error = '';
        $ssac = new SmartStoreApiController();
        $smartstoreAccountId = $this->account->id;
        $duplicated = [];
        foreach ($this->products as $product) {
            $exists = DB::table('smart_store_uploaded_products')
                ->where('smart_store_account_id', $smartstoreAccountId)
                ->where('product_id', $product->id)
                ->where('is_active', 'Y')
                ->exists();
            if ($exists === true) {
                $duplicated[] = $product->productCode;
                continue;
            }
            $uploadImageResult = $this->uploadImageFromUrl($product->productImage, $this->account);
            if ($uploadImageResult['status'] === false) {
                continue;
            }
            $productImage = $uploadImageResult['data']['images'][0]['url'];
            $data = $this->generateParam($product, $productImage);
            $result = $ssac->builder($this->account, 'application/json', 'POST', 'https://api.commerce.naver.com/external/v2/products', $data);
            if ($result['status'] === true) {
                $success++;
                $resultData = $result['data'];
                $originProductNo = $resultData['originProductNo'];
                $smartstoreChannelProductNo = $resultData['smartstoreChannelProductNo'];
                $productId = $product->id;
                $productPrice = $product->productPrice;
                $shippingFee = $product->shipping_fee;
                $productName = $product->productName;
                $this->store($smartstoreAccountId, $productId, $productPrice, $shippingFee, $originProductNo, $smartstoreChannelProductNo, $productName);
            } else {
                $error = $result['error'];
            }
        }
        $status = true;
        $numProducts = count($this->products);
        if ($success < 1) {
            $status = false;
        }
        return [
            'status' => $status,
            'message' => "총 " . $numProducts . " 개의 상품들 중 $success 개의 상품을 성공적으로 업로드했습니다.<br>" . count($duplicated) . "개의 중복 상품을 필터링했습니다.",
            'error' => $error
        ];
    }
    protected function store($smartstoreAccountId, $productId, $productPrice, $shippingFee, $originProductNo, $smartstoreChannelProductNo, $productName)
    {
        DB::table('smart_store_uploaded_products')
            ->insert([
                'smart_store_account_id' => $smartstoreAccountId,
                'product_id' => $productId,
                'origin_product_no' => $originProductNo,
                'smartstore_channel_product_no' => $smartstoreChannelProductNo,
                'price' => $productPrice,
                'shipping_fee' => $shippingFee,
                'product_name' => $productName
            ]);
    }
    private function generateParam($product, $productImage)
    {
        $productKeywords = explode(',', $product->productKeywords);
        $sellerTags = [];
        foreach ($productKeywords as $keyword) {
            $sellerTags[] = [
                'text' => $keyword
            ];
        }
        $data = [
            'originProduct' => [
                'statusType' => 'SALE',
                'leafCategoryId' => $product->code,
                'name' => $product->productName,
                'detailContent' => '<center><img src="https://www.sellwing.kr/images/CDN/ss_header.jpg"><br></center>' . $product->productDetail,
                'images' => [
                    'representativeImage' => [
                        'url' => $productImage
                    ]
                ],
                'salePrice' => $product->productPrice * 2,
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
                        'afterServiceTelephoneNumber' => (string)$this->partner->phone,
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
                            'itemName' => $product->productName,
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
                'channelProductName' => $product->productName,
                'naverShoppingRegistration' => true,
                'channelProductDisplayStatusType' => 'ON'
            ]
        ];
        return $data;
    }
    public function uploadImageFromUrl(string $imageUrl, $account): array
    {
        $imageContent = $this->downloadImage($imageUrl);
        if ($imageContent === false) {
            return [
                'status' => false,
                'message' => '상품 대표 이미지 추출에 실패했습니다.'
            ];
        }

        $tempImagePath = $this->createTempImage($imageContent, $imageUrl);
        if ($tempImagePath === false) {
            return [
                'status' => false,
                'message' => '상품 대표 이미지 추출에 실패했습니다.'
            ];
        }

        $uploadResponse = $this->uploadToAPI($tempImagePath, $imageUrl, $account);
        $this->cleanupTempFile($tempImagePath);

        return $uploadResponse;
    }
    private function downloadImage(string $imageUrl)
    {
        try {
            $response = Http::get($imageUrl);
            return $response->successful() ? $response->body() : false;
        } catch (\Exception $e) {
            return false;
        }
    }
    private function createTempImage(string $imageContent, string $imageUrl): string
    {
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $tempDir = storage_path('app/public/temp-product-images');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $uniqueName = uniqid('upload_', true) . '.' . $extension;
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $uniqueName;

        $tempFile = new \SplFileObject($tempPath, 'w');
        if ($tempFile->fwrite($imageContent) === false) {
            return false;
        }

        return $tempFile->getRealPath();
    }
    private function uploadToAPI(string $tempImagePath, string $imageUrl, $account): array
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
        $response = $this->imageUrlRequest($account, 'multipart/form-data', 'POST', 'https://api.commerce.naver.com/external/v1/product-images/upload', $multipartData);

        // Always close the file handle if it's valid.
        if (is_resource($fileHandle)) {
            fclose($fileHandle);
        }

        return $response;
    }
    private function cleanupTempFile(string $tempImagePath): void
    {
        unlink($tempImagePath);
    }
    private function imageUrlRequest($account, string $contentType, string $method, string $url, array $data): array
    {
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
    // 스마트 스토어 상품 업로드 프로세스 종료.
}
