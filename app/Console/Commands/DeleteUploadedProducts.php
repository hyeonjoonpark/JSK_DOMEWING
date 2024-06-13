<?php

namespace App\Console\Commands;

use App\Http\Controllers\Partners\Products\UploadedController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteUploadedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-uploaded-products';

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
        ini_set('memory_allow', '-1');
        set_time_limit(0);
        // 쿠팡: 40, 스마트 스토어: 51
        $vendorId = 51;
        $accountIds = [21];
        $vendor = DB::table('vendors')
            ->where('id', $vendorId)
            ->first();
        $uc = new UploadedController();
        foreach ($accountIds as $accountId) {
            $originProductsNo = DB::table($vendor->name_eng . '_uploaded_products')
                ->where($vendor->name_eng . '_account_id', $accountId)
                ->pluck('origin_product_no')
                ->toArray();
            $request = new Request([
                'vendorId' => $vendorId,
                'originProductsNo' => $originProductsNo
            ]);
            print_r($uc->delete($request));
            $this->destroy($originProductsNo, $vendor->name_eng . '_uploaded_products');
        }
    }
    protected function destroy($originProductsNo, $table)
    {
        try {
            DB::table($table)
                ->whereIn('origin_product_no', $originProductsNo)
                ->update([
                    'is_active' => 'N'
                ]);
            $this->info('Success');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
