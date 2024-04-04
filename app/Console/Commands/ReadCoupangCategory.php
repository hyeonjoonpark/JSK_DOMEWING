<?php

namespace App\Console\Commands;

use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use Illuminate\Console\Command;

class ReadCoupangCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-coupang-category';

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
        $apiController = new ApiController();
        $method = "GET";
        $path = "/v2/providers/seller_api/apis/api/v1/marketplace/meta/display-categories/59329";
        $accessKey = "ba20bf45-8ba8-4a41-a050-1d158a55bfe9";
        $secretKey = "b9172e81915236896964a5c4e5206688f23023f0";
        $apiResult = $apiController->build($method, $path, $accessKey, $secretKey);
        $data = $apiResult['data'];
        $httpcode = $data['httpcode'];
        $result = $data['result'];
    }
}
