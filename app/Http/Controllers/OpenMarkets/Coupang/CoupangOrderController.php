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

    public function index()
    {
        $orderList = $this->getOrderList('ACCEPT');
        return view('partner.coupang_order_list', ['orderList' => $orderList]);
    }

    public function getOrderList($status)
    {
        $account = $this->getAccount();
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets';
        $query = 'createdAtFrom=2024-04-16&createdAtTo=2024-04-29&status=' . $status;

        $response = $this->ssac->getBuilder($account->access_key, $account->secret_key, 'application/json', $path, $query);

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

    private function getAccount()
    {
        return DB::table('coupang_accounts')->where('partner_id', Auth::guard('partner')->id())->first();
    }
}
