<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Trendhunterb2bCategoryMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trendhunterb2b-category-mapping';

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
        $categoryMapping = DB::table('category_mapping')
            ->pluck('ownerclan')
            ->toArray();
        foreach ($categoryMapping as $categoryId) {
            DB::table('category_mapping')
                ->where('ownerclan', $categoryId)
                ->update([
                    'trendhunterb2b' => $categoryId
                ]);
        }
        echo 'sibal';
    }
}
