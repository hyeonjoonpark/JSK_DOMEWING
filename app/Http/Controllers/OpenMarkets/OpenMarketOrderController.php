<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangOrderController;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpenMarketOrderController extends Controller
{
    public function index(Request $request)
    {
        $marketIds = $request->input('openMarketIds', []);
        $orderwingEngNameLists = $this->getOrderwingOpenMarkets($marketIds);
        $domewingAndPartners = $this->getDomewingAndPartners();
        if (!$domewingAndPartners) {
            return [
                'status' => false,
                'message' => '도매윙 계정연동한 파트너들이 없습니다.',
                'data' => []
            ];
        }
        $results = [];
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        foreach ($domewingAndPartners as $domewingAndPartner) {
            $partner = $this->getPartner($domewingAndPartner->partner_id);
            $domewingUserName = $this->getDomewingUserName($domewingAndPartner->domewing_account_id);
            foreach ($orderwingEngNameLists as $orderwingEngNameList) {
                $methodName = 'call' . ucfirst($orderwingEngNameList) . 'OrderApi';
                if (method_exists($this, $methodName)) {
                    $apiResult = call_user_func([$this, $methodName], $partner->id, $startDate, $endDate);
                } else {
                    $apiResult = null;
                    Log::error("Method $methodName does not exist.");
                }
                $results[] = [
                    'domewing_user_name' => $domewingUserName->username,
                    'api_result' => $apiResult
                ];
            }
        }
        return [
            'status' => true,
            'message' => '성공적으로 오더윙을 가동하였습니다.',
            'data' => $results
        ];
    }
    public function indexPartner(Request $request)
    {
        $apiToken = $request->apiToken;
        $currentPartnerId = DB::table('partners')
            ->where('api_token', $apiToken)
            ->value('id');
        $marketIds = $request->input('openMarketIds', []);
        $orderwingEngNameLists = $this->getOrderwingOpenMarkets($marketIds);
        $domewingAndPartner = $this->getDomewingAndPartners($currentPartnerId);
        if (!$domewingAndPartner) {
            return [
                'status' => false,
                'message' => '도매윙 계정연동을 해야합니다.',
                'data' => []
            ];
        }
        $wing = $this->calcWingById($domewingAndPartner->domewing_account_id);
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $results = [];
        $totalAmountRequired = 0;
        $partner = $this->getPartner($domewingAndPartner->partner_id);
        $domewingUserName = $this->getDomewingUserName($domewingAndPartner->domewing_account_id);
        foreach ($orderwingEngNameLists as $orderwingEngNameList) {
            $methodName = 'call' . ucfirst($orderwingEngNameList) . 'OrderApi';
            if (method_exists($this, $methodName)) {
                $apiResult = call_user_func([$this, $methodName], $partner->id, $startDate, $endDate);
                if (isset($apiResult) && is_array($apiResult)) {
                    foreach ($apiResult as $order) {
                        if ($order['productOrderStatus'] == "결제완료") {
                            $totalAmountRequired += $order['totalPaymentAmount'] + $order['deliveryFeeAmount'];
                        }
                    }
                }
            } else {
                $apiResult = null;
                Log::error("Method $methodName does not exist.");
            }
            $results[] = [
                'domewing_user_name' => $domewingUserName->username,
                'api_result' => $apiResult
            ];
        }
        if ($totalAmountRequired > $wing) {
            return [
                'status' => false,
                'message' => 'wing 잔액이 부족합니다.',
                'data' => $totalAmountRequired - $wing,
            ];
        }
        return [
            'status' => true,
            'message' => '성공적으로 오더윙을 가동하였습니다.',
            'data' => $results
        ];
    }

    private function getOrderwingOpenMarkets($marketIds)
    {
        $result = DB::table('vendors AS v')
            ->whereIn('v.id', $marketIds)
            ->where('v.is_active', 'active')
            ->select('name_eng')
            ->get();

        return $result->pluck('name_eng')->toArray();
    }
    private function getDomewingAndPartners($partnerId = null) //그리고 어떤 도매윙 아이디의 오픈마켓 정보들인가를 가져와
    {
        $query = DB::table('partner_domewing_accounts as da')
            ->where('is_active', 'Y')
            ->select('partner_id', 'domewing_account_id');
        if ($partnerId) {
            return $query->where('partner_id', $partnerId)->first();
        }
        return $query->get();
    }

    private function getPartner($id) // 이걸로 파트너를 가져와서 그 파트너의 오픈마켓들을 싹 돌릴거야
    {
        return DB::table('partners')
            ->where('id', $id)
            ->first();
    }
    private function getDomewingUserName($id) //도매윙 계정을 가져와? 왜? 이름 줄려고
    {
        return DB::table('members')
            ->where('id', $id)
            ->select('username')
            ->first();
    }
    private function calcWingById($id)
    {
        $balanceQuery = DB::table('transaction_wing')
            ->selectRaw('SUM(CASE WHEN type = "DEPOSIT" AND status = "APPROVED" THEN amount ELSE 0 END) AS total_deposit')
            ->selectRaw('SUM(CASE WHEN type = "ORDER" OR type = "WITHDRAW" THEN amount ELSE 0 END) AS total_withdrawal')
            ->where('member_id', $id)
            ->where('status', '!=', 'REJECTED')
            ->get();

        // Calculate the balance
        $totalDeposit = $balanceQuery[0]->total_deposit ?? 0;
        $totalWithdrawal = $balanceQuery[0]->total_withdrawal ?? 0;
        $balance = $totalDeposit - $totalWithdrawal;

        return $balance;
    }
    private function callSmart_storeOrderApi($id, $startDate, $endDate)
    {
        $controller = new SmartStoreOrderController();
        return $controller->index($id, $startDate, $endDate);
    }
    private function callCoupangOrderApi($id, $startDate, $endDate)
    {
        $controller = new CoupangOrderController();
        return $controller->index($id, $startDate, $endDate);
    }
}
