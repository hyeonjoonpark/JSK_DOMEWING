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
        $metaldiyProductCodes = DB::table('minewing_products')
            ->where('sellerID', 2)
            ->pluck('productCode')
            ->toArray();
        $chunkedCodes = array_chunk($metaldiyProductCodes, 500, false);
        foreach ($chunkedCodes as $index => $codes) {
            $codes = join(',', $codes);
            $i = $index + 1;
            $tempFilePath = public_path('assets/txt/metaldiy_product_codes_' . $i . '.txt');
            file_put_contents($tempFilePath, $codes);
        }
        echo "success";
    }
}
