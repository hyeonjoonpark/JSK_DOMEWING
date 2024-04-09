<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SmartStoreCategoryMapping extends Command
{
    protected $signature = 'app:smart-store-category-mapping';
    protected $description = 'Updates category mapping for Smart Store';

    public function handle()
    {
        $categoryMappings = $this->getCategoryMapping();

        foreach ($categoryMappings as $mapping) {
            $smartStoreCategoryId = $this->getSmartStoreCategoryIdByName($mapping->ownerclanCategoryName);

            if ($smartStoreCategoryId) {
                $this->updateCategoryMapping($mapping->ownerclanCategoryId, $smartStoreCategoryId);
            }
        }
    }

    private function getSmartStoreCategoryIdByName($name)
    {
        return DB::table('smart_store_category')
            ->where('name', '=', $name)
            ->value('id'); // Using value() to directly get 'id'
    }

    private function updateCategoryMapping($ownerclanCategoryId, $smartStoreCategoryId)
    {
        DB::table('category_mapping')
            ->where('ownerclan', '=', $ownerclanCategoryId)
            ->update(['smart_store' => $smartStoreCategoryId]);
    }

    private function getCategoryMapping()
    {
        return DB::table('category_mapping')
            ->join('ownerclan_category', 'category_mapping.ownerclan', '=', 'ownerclan_category.id')
            ->select('category_mapping.ownerclan as ownerclanCategoryId', 'ownerclan_category.name as ownerclanCategoryName')
            ->get();
    }
}
