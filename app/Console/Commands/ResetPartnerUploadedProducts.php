<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetPartnerUploadedProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-partner-uploaded-products {partnerId} {vendorId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '특정 파트너의 업로드된 상품들을 데이터베이스로부터 모두 초기화합니다. 실제 오픈 마켓에는 반영되지 않습니다.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $partnerId = $this->argument('partnerId');
        $vendorId = $this->argument('vendorId');
        $vendor = DB::table('vendors')
            ->where('id', $vendorId)
            ->first();
        $accountIds = DB::table($vendor->name_eng . '_accounts')
            ->where('partner_id', $partnerId)
            ->pluck('id')
            ->toArray();
        $uploadedProductIds = DB::table($vendor->name_eng . '_uploaded_products')
            ->whereIn($vendor->name_eng . '_account_id', $accountIds)
            ->pluck('id')
            ->toArray();
        $this->destroy($vendor->name_eng, $uploadedProductIds);
    }
    protected function destroy(string $vendorEngName, array $uploadedProductIds)
    {
        try {
            DB::table($vendorEngName . '_uploaded_products')
                ->whereIn('id', $uploadedProductIds)
                ->update([
                    'is_active' => 'N'
                ]);
            $this->info('Success!');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
