<?php

namespace App\Http\Controllers\OpenMarkets\Coupang;

use App\Http\Controllers\Controller;
use DateInterval;
use DatePeriod;
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
    public function index($id = null, $start = null, $end = null)
    {
        if ($id == null) {
            $id = Auth::guard('partner')->id();
        }
        $orderList = $this->getOrderList($id, $start, $end);
        return $orderList;
    }
    private function getOrderList($id, $start = null, $end = null)
    {
        $account = $this->getAccount($id);
        $startDate = $start ? new DateTime($start) : new DateTime('now - 6 days');
        $endDate = $end ? new DateTime($end) : new DateTime('now');


        $statusMap = [
            'ACCEPT' => '결제완료',
            'INSTRUCT' => '상품준비중',
            'DEPARTURE' => '배송지시',
            'DELIVERING' => '배송중',
            'FINAL_DELIVERY' => '배송완료',
            'NONE_TRACKING' => '업체 직접 배송(배송 연동 미적용), 추적불가',
        ];

        $allOrders = [];
        $interval = new DateInterval('P1M'); // 1 month interval
        $period = new DatePeriod($startDate, $interval, $endDate);
        $allOrders = [];

        foreach ($statusMap as $status => $description) {
            foreach ($period as $dt) {
                $currentStart = $dt;
                $currentEnd = clone $currentStart;
                $currentEnd->add($interval)->sub(new DateInterval('P1D'));

                if ($currentEnd > $endDate) {
                    $currentEnd = $endDate;
                }
                $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets';
                $baseQuery = [
                    'createdAtFrom' => $currentStart->format('Y-m-d'),
                    'createdAtTo' => $currentEnd->format('Y-m-d'),
                    'status' => $status
                ];
                $queryString = http_build_query($baseQuery);
                $response = $this->ssac->getBuilder($account->access_key, $account->secret_key, 'application/json', $path, $queryString);
                $transformedResponse = $this->transformOrderDetails($response);
                $allOrders = array_merge($allOrders, $transformedResponse);
            }
        }

        return $allOrders;
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
                        'orderName' => $item['orderer']['name'],
                        'productName' => $orderItem['vendorItemName'],
                        'quantity' => $orderItem['shippingCount'],
                        'unitPrice' => $orderItem['salesPrice'],
                        'totalPaymentAmount' => $orderItem['orderPrice'],
                        'deliveryFeeAmount' => $item['shippingPrice'],
                        'productOrderStatus' => $this->mapStatusToReadable($item['status']),
                        'orderDate' => isset($item['orderedAt']) ? (new DateTime($item['orderedAt']))->format('Y-m-d H:i:s') : 'N/A',
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
