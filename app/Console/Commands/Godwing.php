<?php

namespace App\Console\Commands;

use App\Http\Controllers\Partners\Products\ManageController;
use App\Http\Controllers\Partners\Products\PartnerTableController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Godwing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:godwing';

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
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $partnerId = 8;
        $vendorIds = [50, 49, 32];
        $ptc = new PartnerTableController();
        $mc = new ManageController();
        $apiToken = DB::table('partners')
            ->where('id', $partnerId)
            ->value('api_token');
        foreach ($vendorIds as $vendorId) {
            $productCodes = DB::table('minewing_products')
                ->where('isActive', 'Y')
                ->where('sellerID', $vendorId)
                ->whereNot('categoryID', null)
                ->pluck('productCode')
                ->toArray();
            $vendorName = DB::table('vendors')
                ->where('id', $vendorId)
                ->value('name');
            $chunkedProductCodes = array_chunk($productCodes, 500, false);
            foreach ($chunkedProductCodes as $i => $chunk) {
                $index = $i + 1;
                $tableName = $vendorName . ' ' . $index;
                $request = new Request([
                    'apiToken' => $apiToken,
                    'productTableName' => $tableName
                ]);
                try {
                    $tableId = $ptc->create($request)['data']['tableId'];
                } catch (\Exception $e) {
                    continue;
                }
                $tableToken = DB::table('partner_tables')
                    ->where('id', $tableId)
                    ->value('token');
                $mc->create($chunk, $tableToken);
            }
        }
        $this->info('Success!');
    }
}
