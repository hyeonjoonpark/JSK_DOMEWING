<?php

namespace App\Http\Controllers\NewMinewing;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MiningController extends Controller
{
    private $controller;
    public function __construct()
    {
        $this->controller = new Controller();
    }
    public function index(Request $request)
    {
        set_time_limit(0);
        $rememberToken = $request->rememberToken;
        $vendorId = $request->vendorID;
        $listUrl = $request->listUrl;
        $seller = $this->controller->getSeller($vendorId);
        $sellerEngName = $seller->name_eng;
        $account = $this->getAccount($rememberToken, $vendorId);
        $username = $account->username;
        $password = $account->password;
        $runScrape = $this->runScrape($sellerEngName, $listUrl, $username, $password);
        if ($runScrape['status'] === false) {
            return $runScrape;
        }
        $products = $runScrape['return'];
        return [
            'status' => true,
            'return' => $products
        ];
    }
    private function runScrape($sellerEngName, $listUrl, $username, $password)
    {
        $scriptPath = public_path('js/minewing/' . $sellerEngName . '.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($listUrl) . " " . escapeshellarg($username) . " " . $password;
        exec($command, $output, $returnCode);
        if ($returnCode === 0 && isset($output[0])) {
            $products = json_decode($output[0], true);
        } else {
            return [
                'status' => false,
                'return' => $listUrl
            ];
        }
        return [
            'status' => true,
            'return' => $products
        ];
    }
    private function getAccount($rememberToken, $vendorId)
    {
        $account = DB::table('accounts')
            ->join('users', 'users.id', '=', 'accounts.user_id')
            ->join('vendors', 'vendors.id', '=', 'accounts.vendor_id')
            ->where('users.remember_token', $rememberToken)
            ->where('vendors.id', $vendorId)
            ->where('accounts.is_active', 'Y')
            ->select('accounts.username', 'accounts.password')
            ->first();
        return $account;
    }
}
