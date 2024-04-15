<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class Store11stCategories extends Command
{
    protected $signature = 'app:create-11st-category';
    protected $description = 'Fetches and stores categories from 11st API';

    public function handle()
    {
        $url = "http://api.11st.co.kr/rest/cateservice/category";
        $response = Http::get($url);

        if ($response->successful()) {
            $categories = $this->parseCategories($response->body());
            $this->insertCategories($categories);
            $this->info('Categories have been successfully fetched and inserted.');
        } else {
            $this->error("Failed to fetch categories: " . $response->status());
        }
    }

    private function parseCategories($xml)
    {
        $categories = [];
        $xml = new SimpleXMLElement($xml);
        $this->buildCategoryPath($xml, $categories);
        return $categories;
    }

    private function buildCategoryPath($xmlElement, &$categories, $path = '')
    {
        foreach ($xmlElement->category as $category) {
            $newPath = $path . ($path ? ' > ' : '') . $category->dispNm;
            if ($category->leafYn == 'Y') {
                $categories[] = [
                    'name' => $newPath,
                    'code' => (string)$category->dispNo
                ];
            } else {
                $this->buildCategoryPath($category, $categories, $newPath);
            }
        }
    }

    private function insertCategories($categories)
    {
        foreach ($categories as $category) {
            DB::table('11st_category')->updateOrInsert(
                ['code' => $category['code']],
                ['name' => $category['name']]
            );
        }
    }
}
