<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $weeklySales = $this->getWeeklySales();
        $actionCenter = $this->getActionCenter();
        return view('admin.dashboard', [
            'weeklySales' => $weeklySales,
            'actionCenter' => $actionCenter
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
}
