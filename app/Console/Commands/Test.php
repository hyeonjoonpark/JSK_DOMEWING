<?php

namespace App\Console\Commands;

use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ac = new ApiController();
        $account = DB::table('coupang_accounts')
            ->where('id', 8)
            ->first();
        $contentType = 'application/json;charset=UTF-8';
        $path = '/v2/providers/seller_api/apis/api/v1/marketplace/seller-products/14904557493';
        //string $accessKey, string $secretKey, string $contentType, string $path, string $query = ''
        $response = $ac->getBuilder($account->access_key, $account->secret_key, $contentType, $path);
        print_r($response);
    }
}
