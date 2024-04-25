<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchShippingFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-shipping-fee';

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
        DB::table('minewing_products AS mp')
            ->join('product_search AS ps', 'ps.vendor_id', '=', 'mp.sellerID')
            ->update([
                'mp.shipping_fee' => DB::raw('ps.shipping_fee')
            ]);
    }
}
