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
        $coupangBanniepetOriginProductsNo = DB::table('smart_store_uploaded_products AS ssup')
            ->join('minewing_products AS mp', 'mp.id', '=', 'ssup.product_id')
            ->where('mp.sellerID', 38)
            ->pluck('ssup.origin_product_no')
            ->toArray();
        $request = new Request([
            'originProductsNo' => $coupangBanniepetOriginProductsNo,
            'vendorId' => 51
        ]);
        $uc = new UploadedController();
        print_r($uc->delete($request));
    }
}
