<?php

namespace App\Console\Commands;

use App\Http\Controllers\SmartStore\SmartStoreAccountController;
use App\Http\Controllers\SmartStore\SmartStoreApiController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use DateTime;

class PullSmartStoreCategories extends Command
{
    protected $signature = 'app:pull-smart-store-categories';
    protected $description = 'Pulls categories from Smart Store and updates them in the local database';

    public function handle()
    {
        $account = $this->getActiveSmartStoreAccount();
        $accessToken = $this->ensureValidAccessToken($account);
        $this->updateCategories($accessToken);
    }

    protected function getActiveSmartStoreAccount()
    {
        return DB::table('smart_store_accounts')
            ->where('is_active', 'ACTIVE')
            ->first(['access_token', 'expired_at', 'application_id', 'secret', 'username', 'id']);
    }

    protected function ensureValidAccessToken($account)
    {
        if (new DateTime($account->expired_at) <= new DateTime()) {
            return $this->refreshAccessToken($account);
        }
        return $account->access_token;
    }

    protected function refreshAccessToken($account)
    {
        $ssac = new SmartStoreAccountController();
        $result = $ssac->getAccessToken($account->application_id, $account->secret, $account->username);
        if ($result['status'] === true) {
            $data = $result['data'];
            $accessToken = $data->access_token;
            $expiresIn = $data->expires_in;

            $this->updateAccessTokenInDb($account->id, $accessToken, $expiresIn);

            return $accessToken;
        } else {
            $this->error("Error refreshing access token: " . $result['error']);
            exit;
        }
    }

    protected function updateAccessTokenInDb($accountId, $accessToken, $expiresIn)
    {
        $expirationTime = (new DateTime())->add(new \DateInterval("PT{$expiresIn}S"))->format("Y-m-d H:i:s");
        DB::table('smart_store_accounts')
            ->where('id', $accountId)
            ->update([
                'access_token' => $accessToken,
                'expired_at' => $expirationTime
            ]);
    }

    protected function updateCategories($accessToken)
    {
        $ssac = new SmartStoreApiController();
        $result = $ssac->build("GET", "/v1/categories", ['last' => true], $accessToken);
        if ($result['status'] === true) {
            $categories = json_decode($result['data']);
            foreach ($categories as $item) {
                DB::table('smart_store_category')->updateOrInsert(
                    ['code' => $item->id],
                    ['name' => $item->wholeCategoryName]
                );
            }
        } else {
            $this->error("Error updating categories: " . $result['error']);
        }
    }
}
