<?php

namespace App\Console\Commands;

use App\Http\Controllers\Partners\Products\UploadedController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteBanniepet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-banniepet';

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
        $coupangBanniepetOriginProductsNo = DB::table('coupang_uploaded_products AS cup')
            ->join('minewing_products AS mp', 'mp.id', '=', 'cup.product_id')
            ->where('mp.sellerID', 38)
            ->pluck('cup.origin_product_no')
            ->toArray();
        $request = new Request([
            'originProductsNo' => $coupangBanniepetOriginProductsNo,
            'vendorId' => 40
        ]);
        $uc = new UploadedController();
        print_r($uc->delete($request));
    }
}
