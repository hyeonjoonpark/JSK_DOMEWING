<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoupangOrderController extends Controller
{
    private $ssac;

    public function __construct()
    {
        $this->ssac = new ApiController();
    }

    public function index($id = null)
    {
        $orderList = $this->getOrderList($id, 'ACCEPT');
        return $orderList;
    }
    public function indexPartner($start = null, $end = null)
    {
        $id = Auth::guard('partner')->id();
        $orderDetails = $this->getOrderList($id, 'ACCEPT', $start, $end);
        return view('partner.coupang_order_list', [
            'orderDetails' => $orderDetails
        ]);
    }

    public function getOrderList($id, $status, $start = null, $end = null)
    {
        $account = $this->getAccount($id);
        $startDate = $start ? new DateTime($start) : new DateTime('now - 6 days');
        $endDate = $end ? new DateTime($end) : new DateTime('now');
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets';
        $baseQuery = [
            'createdAtFrom' => $startDate->format('Y-m-d'),
            'createdAtTo' => $endDate->format('Y-m-d'),
            'status' => $status
        ];
        $queryString = http_build_query($baseQuery);
        $response = $this->ssac->getBuilder($account->access_key, $account->secret_key, 'application/json', $path, $queryString);
        return $this->transformOrderDetails($response);
    }


    private function transformOrderDetails($response)
    {
        $orderDetails = [];
        if (isset($response['data']['data'])) {
            foreach ($response['data']['data'] as $item) {
                foreach ($item['orderItems'] as $orderItem) {
                    $orderDetails[] = [
                        'productOrderId' => $orderItem['vendorItemId'],
                        'market' => '쿠팡',
                        'orderId' => $item['orderId'],
                        'ordererName' => $item['orderer']['name'],
                        'productName' => $orderItem['vendorItemName'],
                        'quantity' => $orderItem['shippingCount'],
                        'unitPrice' => $orderItem['salesPrice'],
                        'totalPaymentAmount' => $orderItem['orderPrice'],
                        'deliveryFeeAmount' => $item['shippingPrice'],
                        'productOrderStatus' => $this->mapStatusToReadable($item['status']),
                        'orderDate' => $item['orderedAt']
                    ];
                }
            }
        }
        return $orderDetails;
    }
    private function mapStatusToReadable($status)
    {
        $statusMap = [
            'ACCEPT' => '결제완료',
            'INSTRUCT' => '상품준비중',
            'DEPARTURE' => '배송지시',
            'DELIVERING' => '배송중',
            'FINAL_DELIVERY' => '배송완료',
            'NONE_TRACKING' => '업체 직접 배송(배송 연동 미적용), 추적불가',
        ];
        return $statusMap[$status] ?? '상태미정';
    }

    private function getAccount($id)
    {
        return DB::table('coupang_accounts')->where('partner_id', $id)->first();
    }
}
