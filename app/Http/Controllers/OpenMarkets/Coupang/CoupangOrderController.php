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
        $accounts = $this->getAccounts($id);
        if (!$accounts) {
            return false;
        }
        $startDate = $start ? new DateTime($start) : new DateTime('now - 4 days');
        $endDate = $end ? new DateTime($end) : new DateTime('now');
        $statusMap = [
            'INSTRUCT' => '상품준비중',
            'ACCEPT' => '결제완료',
            'DEPARTURE' => '배송지시',
            'DELIVERING' => '배송중',
            'FINAL_DELIVERY' => '배송완료',
            'NONE_TRACKING' => '업체 직접 배송(배송 연동 미적용), 추적불가',
        ];
        $allOrders = [];
        $interval = new DateInterval('P1M'); // 1 month interval
        $period = new DatePeriod($startDate, $interval, $endDate);
        foreach ($accounts as $account) {
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
                    if (!$response) continue;
                    if (isset($response['error'])) {
                        continue; // 오류가 있으면 다음 계정으로 넘어감
                    }
                    $shipmentBoxIds = $this->collectShipmentBoxIds($response);
                    $setProduct = $this->setProductAsPreparing($account, $shipmentBoxIds); //상품준비중처리
                    if (!$setProduct['status']) {
                        return [
                            'status' => false,
                            'message' => '상품준비중으로 처리중 오류가 발생하였습니다.',
                            'data' => $setProduct
                        ];
                    }
                    $transformedResponse = $this->transformOrderDetails($response, $account);
                    if ($transformedResponse !== false) {
                        $allOrders = array_merge($allOrders, $transformedResponse);
                    }
                }
            }
        }
        return $allOrders;
    }
    private function transformOrderDetails($response, $account)
    {
        if (!isset($response['data']['data'])) {
            return false;
        }
        $orderDetails = [];
        if (!empty($response['data']['data'])) {
            foreach ($response['data']['data'] as $item) {
                foreach ($item['orderItems'] as $orderItem) {
                    $orderDetails[] = [
                        'market' => '쿠팡',
                        'marketEngName' => 'coupang',
                        'orderId' => strval($item['orderId']),
                        'productOrderId' => strval($item['shipmentBoxId']),
                        'orderName' => $item['orderer']['name'],
                        'productName' => $orderItem['vendorItemName'],
                        'quantity' => $orderItem['shippingCount'],
                        'unitPrice' => $orderItem['salesPrice'],
                        'totalPaymentAmount' => $orderItem['orderPrice'],
                        'deliveryFeeAmount' => $item['shippingPrice'],
                        'productOrderStatus' => $this->mapStatusToReadable($item['status']),
                        'orderDate' => isset($item['paidAt']) ? (new DateTime($item['paidAt']))->format('Y-m-d H:i:s') : 'N/A',
                        'receiverName' => $item['receiver']['name'],
                        'receiverPhone' => $item['receiver']['safeNumber'],
                        'postCode' => $item['receiver']['postCode'],
                        'address' => $item['receiver']['addr1'] . ' ' . $item['receiver']['addr2'],
                        'addressName' => '기본배송지',
                        'productCode' => $orderItem['externalVendorSkuCode'] ?? 'N/A',
                        'remark' => $item['parcelPrintMessage'] ?? 'N/A',
                        'accountId' => $account->id
                    ];
                }
            }
        }
        return $orderDetails;
    }
    private function setProductAsPreparing($account, $productOrderNumber) //상품준비중처리
    {
        $accessKey = $account->access_key;
        $secretKey = $account->secret_key;
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/openapi/apis/api/v4/vendors/' . $account->code . '/ordersheets/acknowledgement';
        $data = [
            'vendorId' => $account->code,
            'shipmentBoxIds' => $productOrderNumber
        ];
        return $this->ssac->putBuilder($accessKey, $secretKey, $contentType, $path, $data);
    }
    private function collectShipmentBoxIds($response)
    {
        if (!isset($response['data']['data'])) {
            return [];
        }
        $shipmentBoxIds = [];
        if (!empty($response['data']['data'])) {
            foreach ($response['data']['data'] as $item) {
                if (isset($item['shipmentBoxId'])) {
                    $shipmentBoxIds[] = $item['shipmentBoxId'];
                }
            }
        }
        return $shipmentBoxIds;
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
    private function getAccounts($id)
    {
        return DB::table('coupang_accounts')
            ->where('partner_id', $id)
            ->where('is_active', 'ACTIVE')
            ->get();
    }
}
