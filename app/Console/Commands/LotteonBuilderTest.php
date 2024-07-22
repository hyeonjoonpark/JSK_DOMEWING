<?php

namespace App\Console\Commands;

use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnApiController;
use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnUploadController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LotteonBuilderTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:lotteon-builder-test';

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
        $products = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->limit(2)
            ->get();
        $partner = DB::table('partners')
            ->where('id', 13)
            ->first();
        $account = DB::table('lotte_on_accounts')
            ->where('id', 1)
            ->first();
        $louc = new LotteOnUploadController($products, $partner, $account);
        print_r($louc->requestDvCstPolNo());
    }
}
