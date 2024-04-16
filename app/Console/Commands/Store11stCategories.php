<?php

namespace App\Console\Commands;

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
        $url = 'http://api.11st.co.kr/rest/cateservice/category'; // Use an environment variable
        $response = Http::get($url);

        if ($response->successful()) {
            try {
                $categories = $this->parseCategories($response->body());
                $this->insertCategories($categories);
                $this->info('Categories have been successfully fetched and inserted.');
            } catch (\Exception $e) {
                $this->error("Parsing or insertion failed: " . $e->getMessage());
            }
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
            if ($category->leafYn == 'Y') { // 최하위 노드만 추가
                $categories[] = [
                    'name' => $newPath,
                    'code' => (string)$category->dispNo
                ];
            } else {
                $this->buildCategoryPath($category, $categories, $newPath); // 최하위가 아니면 계속 순회
            }
        }
    }

    private function insertCategories($categories)
    {
        try {
            print_r($categories);
            DB::table('st11_category')->upsert($categories, ['code'], ['name']); // Use upsert for performance improvement
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
