<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecoverCategoryMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recover-category-mapping';

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
        $mappedCategories = DB::table('category_mapping')
            ->pluck('ownerclan')
            ->toArray();
        $categories = DB::table('minewing_products')
            ->whereNotIn('categoryID', $mappedCategories)
            ->groupBy('categoryID')
            ->pluck('categoryID')
            ->toArray();
        foreach ($categories as $categoryId) {
            DB::table('category_mapping')
                ->insert([
                    'ownerclan' => $categoryId
                ]);
        }
    }
}
