<?php

namespace App\Http\Controllers\OpenMarkets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SmartStore\SmartStoreOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpenMarketOrderController extends Controller
{
    public function index(Request $request)
    {
        $orderwingLists = $this->getOrderwingOpenMarkets($request);
        $domewingAndPartners = $this->getDomewingAndPartners();
        $results = [];
        foreach ($domewingAndPartners as $domewingAndPartner) { // 파트너, 도매윙 연동한 계정들을 확인
            $partner = $this->getPartner($domewingAndPartner->partner_id); // 연동한 계정의 파트너 계정 가져오기
            $domewingUserName = $this->getDomewingUserName($domewingAndPartner->domewing_account_id); //연동한 계정의 도매윙 유저네임 가져오기
            foreach ($orderwingLists as $orderwingList) { // 오픈마켓 반복문을 돌리면서 오픈마켓들의 orderwing 진행
                //오픈마켓이랑 orderController연동해서 사용하면됌 그리고 결과값을 배열에 담아서 리턴
                $smartStoreReuslt = $this->callSmartStoreOrderApi($partner->id);
                $results[] = [
                    'domewing_user_name' => $domewingUserName->username,
                    'api_result' => $smartStoreReuslt
                ];
            }
        }
        return response()->json([
            'message' => 'Orders processed successfully',
            'data' => $results
        ]);
    }
    public function getOrderwingOpenMarkets(Request $request) //이걸로 선택한 오픈마켓 리스트들을 가져와
    {
        return DB::table('vondros AS v')
            // ->where('v.id', $request->id)
            ->where('v.id', '51')
            ->where('v.is_active', 'active')
            ->get();
    }
    public function getDomewingAndPartners() //그리고 어떤 도매윙 아이디의 오픈마켓 정보들인가를 가져와
    {
        return DB::table('partner_domewing_accounts as da')
            ->where('is_active', 'Y')
            ->select('partner_id', 'domewing_account_id')
            ->get();
    }
    public function getPartner($id) // 이걸로 파트너를 가져와서 그 파트너의 오픈마켓들을 싹 돌릴거야
    {
        return DB::table('partners')
            ->where('id', $id)
            ->first();
    }
    public function getDomewingUserName($id) //도매윙 계정을 가져와? 왜? 이름 줄려고
    {
        return DB::table('members')
            ->where('id', $id)
            ->select('username')
            ->first();
    }
    private function callSmartStoreOrderApi($id)
    {
        return new SmartStoreOrderController($id);
    }
    private function callCoupangOrderApi()
    {
    }
}
