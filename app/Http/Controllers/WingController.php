<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WingController extends Controller
{
    public function getBalance($memberId)
    {
        $result = DB::table('transaction_wing')
            ->selectRaw('
                    SUM(CASE WHEN type = "DEPOSIT" AND status = "APPROVED" THEN amount ELSE 0 END) AS total_deposits,
                    SUM(CASE WHEN type IN ("ORDER", "WITHDRAW") THEN amount ELSE 0 END) AS total_withdrawals
                ')
            ->where('member_id', $memberId)
            ->where('status', '!=', 'REJECTED')
            ->first();
        $totalDeposits = $result->total_deposits ?? 0;
        $totalWithdrawals = $result->total_withdrawals ?? 0;
        return $totalDeposits - $totalWithdrawals;
    }
}
