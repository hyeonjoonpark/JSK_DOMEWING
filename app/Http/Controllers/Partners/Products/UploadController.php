<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangUploadController;
use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnUploadController;
use App\Http\Controllers\OpenMarkets\St11\UploadController as St11UploadController;
use App\Http\Controllers\SmartStore\SmartstoreProductUpload;
use App\Jobs\ProcessProductUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = Auth::guard('partner')->id();
        // 연동된 도매윙 계정이 있는지 검사.
        $hasSync = DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        if ($hasSync === false) {
            return redirect('/partner/account-setting/dowewing-integration/');
        }
        // 생성된 상품 테이블이 있는지 검사.
        $hasTable = DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        if ($hasTable === false) {
            // 없을시, 상품 관리관으로 리다이렉트.
            return redirect('/partner/products/manage');
        }
        $openMarkets = DB::table('vendors AS v')
            ->join('vendor_commissions AS vc', 'vc.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('v.type', 'OPEN_MARKET')
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
    public function create(Request $request) //이걸로 데이터 올리는중
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');

        // 데이터 유효성 검사.
        $validator = Validator::make($request->all(), [
            'partnerTableToken' => 'required|string',
            'vendorId' => 'required|integer',
            'partnerMargin' => 'required|integer|max:99',
            'accountHash' => 'required|string',
            'vendorCommission' => 'required|numeric'
        ], [
            'partnerTableToken' => '상품 업로드를 위한 상품 테이블을 생성해주세요.',
            'vendorId' => '상품 업로드를 위한 오픈 마켓을 선택해주세요.',
            'partnerMargin.required' => '마진율을 입력해 주세요.',
            'partnerMargin.integer' => '마진율은 정수여야 합니다.',
            'partnerMargin.max' => '마진율은 99를 초과할 수 없습니다.',
            'accountHash' => '계정을 선택해주세요.',
            'vendorCommission' => '올바른 마켓 수수료(%)를 기입해주세요.'
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        }

        // 데이터 전처리: 파라미터
        $partnerTableToken = $request->partnerTableToken;
        $vendorId = $request->vendorId;
        $vendorCommission = $request->vendorCommission;
        $vendor = DB::table('vendors')
            ->where('id', $vendorId)
            ->where('is_active', 'ACTIVE')
            ->first();

        if ($vendor === null) {
            return [
                'status' => false,
                'message' => '비활성화된 오픈 마켓입니다. 다시 시도해주세요.'
            ];
        }

        $vendorEngName = $vendor->name_eng;
        $partnerMargin = $request->partnerMargin;
        $partnerMarginRate = $partnerMargin / 100 + 1;
        $margin = DB::table('sellwing_config')
            ->where('id', 1)
            ->first(['value'])
            ->value;
        $marginRate = $margin / 100 + 1;
        $commissionRate = $vendorCommission / 100 + 1;
        $products = DB::table('partner_products AS pp')
            ->join('partner_tables AS pt', 'pt.id', '=', 'pp.partner_table_id')
            ->join('minewing_products AS mp', 'mp.id', '=', 'pp.product_id')
            ->join('category_mapping AS cm', 'cm.ownerclan', '=', 'mp.categoryID')
            ->join($vendorEngName . '_category AS c', 'c.id', '=', 'cm.' . $vendorEngName)
            ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
            ->where('pt.is_active', 'Y')
            ->where('pt.token', $partnerTableToken)
            ->where('mp.isActive', 'Y')
            ->whereNotNull('mp.categoryID')
            ->select([
                DB::raw("CEIL((mp.productPrice * $marginRate * $partnerMarginRate * $commissionRate) / 10) * 10 AS productPrice"),
                'mp.productCode',
                'pp.product_name AS productName',
                'mp.productImage',
                'mp.productDetail',
                'c.code',
                'mp.shipping_fee',
                'ps.additional_shipping_fee',
                'mp.id',
                'mp.productKeywords',
                'mp.hasOption',
                'mp.bundle_quantity',
                'mp.categoryID'
            ])
            ->get();

        if ($products->isEmpty()) {
            return [
                'status' => false,
                'message' => '테이블이 비어 있거나 모든 상품이 이미 업로드되었습니다.'
            ];
        }

        $partner = DB::table('partners')
            ->where('api_token', $request->apiToken)
            ->first();

        $account = DB::table($vendorEngName . '_accounts')
            ->where('hash', $request->accountHash)
            ->first();
        $tableName = DB::table('partner_tables')
            ->where('token', $partnerTableToken)
            ->value('title');
        // 각 큐의 현재 대기열 길이 확인
        $queueLengths = [];
        for ($i = 1; $i < 11; $i++) {
            $queueName = 'uploads' . $i;
            $queueLengths[$queueName] = DB::table('jobs')->where('queue', $queueName)->count();
        }
        // 가장 대기열이 짧은 큐 선택
        $currentQueue = array_keys($queueLengths, min($queueLengths))[0];
        ProcessProductUpload::dispatch($products, $partner, $account, $vendor, $tableName)->onQueue($currentQueue);
        $numJobs = $queueLengths[$currentQueue];
        $queueIndex = array_search($currentQueue, array_keys($queueLengths)) + 1;
        return [
            'status' => true,
            'message' => '총 ' . count($products) . '개의 상품 업로드 요청이 성공적으로 큐에 배치되었습니다.<br>' .
                '요청이 ' . $queueIndex . '번 채널에 배치되었습니다.<br>' .
                '현재 ' . $numJobs . '개의 작업이 대기열에 있습니다.'
        ];
    }
}
