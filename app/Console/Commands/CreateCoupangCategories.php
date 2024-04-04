<?php

namespace App\Console\Commands;

use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateCoupangCategories extends Command
{
    protected $signature = 'app:create-coupang-categories';
    protected $description = 'Imports Coupang categories into the database.';

    public function handle()
    {
        $apiController = new ApiController();
        $method = "GET";
        $path = "/v2/providers/seller_api/apis/api/v1/marketplace/meta/display-categories";
        $accessKey = "ba20bf45-8ba8-4a41-a050-1d158a55bfe9";
        $secretKey = "b9172e81915236896964a5c4e5206688f23023f0";
        $apiReturn = $apiController->build($method, $path, $accessKey, $secretKey);

        if ($apiReturn['status'] === true) {
            $coupangCategories = $apiReturn['data']->data;
            $this->insertCategories($coupangCategories->child);
        } else {
            $this->error("Failed to fetch categories from Coupang.");
        }
    }

    private function insertCategories($categories, $prefix = '')
    {
        foreach ($categories as $category) {
            if ($category->status === 'ACTIVE') {
                $categoryPath = $prefix === '' ? $category->name : $prefix . ' > ' . $category->name;
                DB::table('coupang_category')->insert([
                    'name' => $categoryPath,
                    'code' => $category->displayItemCategoryCode,
                ]);

                if (!empty($category->child)) {
                    $this->insertCategories($category->child, $categoryPath);
                }
            }
        }
    }
}
