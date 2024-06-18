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
        return view('admin.dashboard');
    }

    public function getActionCenter()
    {
        set_time_limit(0);
        $numPendingOrders = $this->countPendingOrders();
        $numContactUs = $this->countPendingContactUs();
        $numPendingDeposits = $this->countPendingDeposits();
        return [
            'numPendingOrders' => $numPendingOrders,
            'numContactUs' => $numContactUs,
            'numPendingDeposits' => $numPendingDeposits,
        ];
    }

    private function countPendingOrders()
    {
        return DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'wt.id', '=', 'o.wing_transaction_id')
            ->where('o.delivery_status', 'PENDING')
            ->whereNotIn('o.type', ['CANCELLED'])
            ->whereNotIn('wt.status', ['REJECTED'])
            ->count();
    }

    private function countPendingContactUs()
    {
        return DB::table('sellwing_contact_us')
            ->where('status', 'PENDING')
            ->count();
    }

    private function countPendingDeposits()
    {
        return DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'dd.wing_transaction_id', '=', 'wt.id')
            ->where('dd.payment_method_id', 5)
            ->where('wt.type', 'DEPOSIT')
            ->where('wt.status', 'PENDING')
            ->count();
    }

    public function getWeeklySales()
    {
        set_time_limit(0);
        $labels = [];
        $recharges = [];
        $sales = [];
        $currentDate = new DateTime();
        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $currentDate)->modify("-$i days");
            $startDatetime = $date->format('Y-m-d 00:00:00');
            $endDatetime = $date->format('Y-m-d 23:59:59');
            $sales[] = $this->sumSales($startDatetime, $endDatetime);
            $recharges[] = $this->sumRecharges($startDatetime, $endDatetime);
            $labels[] = $date->format('d') . '일';
        }

        $maxTarget = max(max($sales), max($recharges));
        $max = ceil($maxTarget / 500000) * 500000;

        $thisMonth = $this->getDateRangeForThisMonth();
        $lastMonth = $this->getDateRangeForLastMonth();

        $thisMonthSaleTotal = $this->sumSales($thisMonth['start'], $thisMonth['end']);
        $lastMonthSaleTotal = $this->sumSales($lastMonth['start'], $lastMonth['end']);
        $thisMonthRechargeTotal = $this->sumRecharges($thisMonth['start'], $thisMonth['end']);
        $lastMonthRechargeTotal = $this->sumRecharges($lastMonth['start'], $lastMonth['end']);

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

    private function sumSales($startDatetime, $endDatetime)
    {
        $paidAmount = DB::table('orders AS o')
            ->join('carts AS c', 'c.id', '=', 'o.cart_id')
            ->where('o.type', 'PAID')
            ->whereBetween('o.created_at', [$startDatetime, $endDatetime])
            ->selectRaw('
                SUM(
                    o.price_then * c.quantity +
                    o.shipping_fee_then * CEIL(
                        CASE
                            WHEN o.bundle_quantity_then = 0
                            THEN 1
                            ELSE c.quantity / o.bundle_quantity_then
                        END
                    )
                ) AS total
            ')
            ->value('total');
        $refundAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('o.type', 'REFUND')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$startDatetime, $endDatetime])
            ->sum('wt.amount');
        return $paidAmount - $refundAmount;
    }

    private function sumRecharges($startDatetime, $endDatetime)
    {
        return DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$startDatetime, $endDatetime])
            ->sum('wt.amount');
    }

    private function getDateRangeForThisMonth()
    {
        return [
            'start' => date('Y-m-01 00:00:00'),
            'end' => date('Y-m-t 23:59:59')
        ];
    }

    private function getDateRangeForLastMonth()
    {
        return [
            'start' => date('Y-m-01 00:00:00', strtotime('-1 month')),
            'end' => date('Y-m-t 23:59:59', strtotime('-1 month'))
        ];
    }

    public function getTop6MemberSales()
    {
        set_time_limit(0);
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

        return array_slice($memberSales, 0, 6);
    }

    private function getMemberSales(int $memberId, array $dateRange): int
    {
        $paidAmount = DB::table('orders AS o')
            ->join('carts AS c', 'c.id', '=', 'o.cart_id')
            ->where('o.type', 'PAID')
            ->where('c.member_id', $memberId)
            ->whereBetween('o.created_at', $dateRange)
            ->selectRaw('
                SUM(
                    o.price_then * c.quantity +
                    o.shipping_fee_then * CEIL(
                        CASE
                            WHEN o.bundle_quantity_then = 0
                            THEN 1
                            ELSE c.quantity / o.bundle_quantity_then
                        END
                    )
                ) AS total
            ')
            ->value('total');
        $refundAmount = DB::table('orders AS o')
            ->join('wing_transactions AS wt', 'o.wing_transaction_id', '=', 'wt.id')
            ->where('wt.member_id', $memberId)
            ->where('o.type', 'REFUND')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', $dateRange)
            ->sum('wt.amount');
        return $paidAmount - $refundAmount;
    }
}
