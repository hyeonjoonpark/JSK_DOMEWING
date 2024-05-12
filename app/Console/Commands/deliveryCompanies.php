<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class deliveryCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delivery-companies';

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
        $companies = [
            ['code' => 'HYUNDAI', 'name' => '롯데택배'],
            ['code' => 'KGB', 'name' => '로젠택배'],
            ['code' => 'EPOST', 'name' => '우체국'],
            ['code' => 'HANJIN', 'name' => '한진택배'],
            ['code' => 'CJGLS', 'name' => 'CJ대한통운'],
            ['code' => 'KDEXP', 'name' => '경동택배'],
            ['code' => 'DIRECT', 'name' => '업체직송'],
            ['code' => 'ILYANG', 'name' => '일양택배'],
            ['code' => 'CHUNIL', 'name' => '천일특송'],
            ['code' => 'AJOU', 'name' => '아주택배'],
            ['code' => 'CSLOGIS', 'name' => 'SC로지스'],
        ];



        foreach ($companies as $company) {
            DB::table('delivery_companies')->updateOrInsert(
                ['name' => $company['name']],
                ['coupang' => $company['code']]
            );
        }
        $this->info('Delivery companies have been successfully inserted into the database.');
    }
}
