<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function index(Request $request)
    {
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
            ->get(['mp.productName', 'mp.productPrice', 'mp.productImage', 'mp.productDetail', 'vc.code', 'ps.shipping_fee', 'ps.additional_shipping_fee']);
        return [
            'status' => true,
            $this->$vendorEngName($products, $partnerId)
        ];
    }
    private function smart_store($products, $partnerId)
    {
        $partner = DB::table('partners')->where('id', $partnerId)->first();
        $margin = DB::table('sellwing_config')->where('id', 1)->value('value');
        $marginRate = $margin / 100 + 1;
        $product = $products[0];

        // $data = [
        //     'originProduct' => [
        //         'name' => $product->productName,
        //         'detailContent' => $product->productDetail,
        //         'leafCategoryId' => $product->code,
        //         'statusType' => 'SALE',
        //         'saleType' => 'NEW',
        //         'images' => [
        //             'representativeImage' => [
        //                 'url' => $product->productImage
        //             ],
        //         ],
        //         'salePrice' => ceil($product->productPrice * $marginRate),
        //         'stockQuantity' => 9999,
        //         'deliveryInfo' => [
        //             'deliveryType' => 'DELIVERY',
        //             'deliveryAttributeType' => 'TODAY',
        //             'deliveryCompany' => 'KGB',
        //             'deliveryBundleGroupUsable' => true,
        //             'deliveryBundleGroupId' => null,
        //             'quickServiceAreas' => null,
        //             'deliveryFee' => [
        //                 'deliveryFeeType' => 'RANGE_QUANTITY_PAID',
        //                 'baseFee' => $product->shipping_fee,
        //                 'deliveryFeePayType' => 'PREPAID',
        //                 'deliveryFeeByArea' => [
        //                     'deliveryAreaType' => 'AREA_2',
        //                     'area2extraFee' => $product->additional_shipping_fee
        //                 ]
        //             ]
        //         ],
        //         'detailAttribute' => [
        //             'afterServiceInfo' => [
        //                 'afterServiceTelephoneNumber' => $partner->phone,
        //                 'afterServiceGuideContent' => '유선문의'
        //             ],
        //             'originalAreaInfo' => [
        //                 'originAreaCode' => '03'
        //             ],
        //             'minorPurchasable' => true
        //         ],
        //         'customerBenefit' => [],
        //         'smartstoreChannelProduct' => [
        //             'channelProductName' => $product->productName,
        //             'naverShoppingRegistration' => false,
        //             'channelProductDisplayStatusType' => 'ON',
        //         ],
        //         'claimDeliveryInfo' => [
        //             'returnDeliveryFee' => $product->shipping_fee,
        //             'exchangeDeliveryFee' => $product->shipping_fee
        //         ]
        //     ]
        // ];

        $data = '{
            "originProducts":{
                "statusType": "SALE"
            }
        }';

        $ssac = new SmartStoreApiController();
        return $ssac->builder($partnerId, 'application/json', 'POST', 'https://api.commerce.naver.com/external/v2/products', $data);
    }
}
