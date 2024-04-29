<?php

namespace App\Console\Commands;

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
        DB::table('coupang_uploaded_products AS cup')
            ->join('partner_products AS pp', 'pp.product_id', '=', 'cup.product_id')
            ->update([
                'cup.product_name' => DB::raw('pp.product_name')
            ]);
    }
}
