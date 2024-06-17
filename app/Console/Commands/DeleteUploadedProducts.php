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
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        // 쿠팡: 40, 스마트 스토어: 51
        $vendorId = 51;
        $accountIds = [46];
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
            $ucDeleteResultFilePath = public_path('/assets/json/delete-uploaded-products-results/');
            if (!is_dir($ucDeleteResultFilePath)) {
                mkdir($ucDeleteResultFilePath);
            }
            file_put_contents($ucDeleteResultFilePath . $vendor->name_eng . '_' . date("YmdHis") . '.json', json_encode($uc->delete($request), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->destroy($originProductsNo, $vendor->name_eng . '_uploaded_products');
        }
        $this->info('COMPLETED!');
    }
    protected function destroy($originProductsNo, $table)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        try {
            DB::table($table)
                ->whereIn('origin_product_no', $originProductsNo)
                ->update([
                    'is_active' => 'N'
                ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
