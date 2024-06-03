<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $weeklySales = $this->getWeeklySales();
        $actionCenter = $this->getActionCenter();
        $top6MemberSales = $this->getTop6MemberSales();
        return view('admin.dashboard', [
            'weeklySales' => $weeklySales,
            'actionCenter' => $actionCenter,
            'top6MemberSales' => $top6MemberSales
        ]);
    }
    private function getActionCenter()
    {
        $numPendingOrders = DB::table('orders')
            ->where('delivery_status', 'PENDING')
            ->count();
        $numContactUs = DB::table('sellwing_contact_us')
            ->where('status', 'PENDING')
            ->count();
        $numPendingDeposits = DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'dd.wing_transaction_id', '=', 'wt.id')
            ->where('dd.payment_method_id', 5)
            ->where('wt.type', 'DEPOSIT')
            ->where('wt.status', 'PENDING')
            ->count();
        return [
            'numPendingOrders' => $numPendingOrders,
            'numContactUs' => $numContactUs,
            'numPendingDeposits' => $numPendingDeposits,
        ];
    }
    private function getWeeklySales()
    {
        $labels = [];
        $recharges = [];
        $sales = [];
        $currentDate = new DateTime();
        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $currentDate)->modify("-$i days");
            $startDatetime = $date->format('Y-m-d 00:00:00');
            $endDatetime = $date->format('Y-m-d 23:59:59');
            $sale = DB::table('wing_transactions AS wt')
                ->join('orders AS o', 'wt.id', '=', 'o.wing_transaction_id')
                ->where('o.type', 'PAID')
                ->where('wt.status', 'APPROVED')
                ->whereBetween('wt.created_at', [$startDatetime, $endDatetime])
                ->sum('wt.amount');
            $recharge = DB::table('wing_transactions AS wt')
                ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
                ->where('wt.status', 'APPROVED')
                ->whereBetween('wt.created_at', [$startDatetime, $endDatetime])
                ->sum('wt.amount');
            $labels[] = $date->format('d') . '일';
            $sales[] = $sale;
            $recharges[] = $recharge;
        }
        $maxTarget = max($sales) > max($recharges) ? max($sales) : max($recharges);
        $max = ceil($maxTarget / 500000) * 500000;
        $thisMonthStart = date('Y-m-01 00:00:00');
        $thisMonthEnd = date('Y-m-t 23:59:59');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));
        $thisMonthSaleTotal = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'wt.id', '=', 'o.wing_transaction_id')
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('wt.amount');
        $lastMonthSaleTotal = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'wt.id', '=', 'o.wing_transaction_id')
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('wt.amount');
        $thisMonthRechargeTotal = DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('wt.amount');
        $lastMonthRechargeTotal = DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('wt.amount');
        return [
            'labels' => $labels,
            'sales' => $sales,
            'recharges' => $recharges,
            'max' => $max,
            'thisMonthSaleTotal' => $thisMonthSaleTotal,
            'lastMonthSaleTotal' => $lastMonthSaleTotal,
            'thisMonthRechargeTotal' => $thisMonthRechargeTotal,
            'lastMonthRechargeTotal' => $lastMonthRechargeTotal,
        ];
    }
    public function getTop6MemberSales()
    {
        $members = DB::table('members AS m')
            ->join('profile_picture AS pp', 'pp.id', '=', 'm.profile_picture')
            ->where('m.is_active', 'ACTIVE')
            ->get(['m.id', 'm.last_name', 'm.first_name', 'm.email', 'm.phone_number', 'pp.profile_picture']);

        $memberSales = [];

        foreach ($members as $m) {
            $total = $this->getMemberSales($m->id, ['1900-01-01 00:00:00', '2100-12-31 23:59:59']);
            $thisMonth = $this->getMemberSales($m->id, [Carbon::now()->startOfMonth()->toDateTimeString(), Carbon::now()->endOfMonth()->toDateTimeString()]);
            $lastMonth = $this->getMemberSales($m->id, [Carbon::now()->subMonth()->startOfMonth()->toDateTimeString(), Carbon::now()->subMonth()->endOfMonth()->toDateTimeString()]);

            $memberSales[] = [
                'total' => $total,
                'thisMonth' => $thisMonth,
                'lastMonth' => $lastMonth,
                'member' => [
                    'name' => $m->last_name . $m->first_name,
                    'email' => $m->email,
                    'phone' => $m->phone_number,
                    'profilePicture' => $m->profile_picture
                ]
            ];
        }
        usort($memberSales, function ($a, $b) {
            return $b['thisMonth'] <=> $a['thisMonth'];
        });

        $getTop6MemberSales = array_slice($memberSales, 0, 6);
        return $getTop6MemberSales;
    }
    public function getMemberSales(int $memberId, array $whereBetween): int
    {
        $paidAmount = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('member_id', $memberId)
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', $whereBetween)
            ->distinct()
            ->sum('wt.amount');
        $refundAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'REFUND')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', $whereBetween)
            ->sum('wt.amount');
        $exchangeAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'EXCHANGE')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', $whereBetween)
            ->sum('wt.amount');
        $sales = $paidAmount - ($refundAmount > 0 ? $refundAmount : -$refundAmount) - ($exchangeAmount > 0 ? $exchangeAmount : -$exchangeAmount);
        return $sales;
    }
}
