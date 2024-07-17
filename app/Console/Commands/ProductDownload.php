<?php

namespace App\Console\Commands;

use App\Http\Controllers\Product\DownloadController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-download';

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
        $productCodes = DB::table('minewing_products')
            ->where('sellerID', 13)
            ->where('remark', '2024-07-17 전선 상품가 업데이트')
            ->where('isActive', 'Y')
            ->pluck('productCode')
            ->toArray();
        print_r($productCodes);
        $dc = new DownloadController();
        $request = new Request([
            'productCodes' => $productCodes
        ]);
        print_r($dc->main($request));
    }
}
