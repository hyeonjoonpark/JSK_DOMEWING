<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class ImportSt11Categories extends Command
{
    protected $signature = 'app:import-st11-categories';
    protected $description = 'Import categories from ST11 API';

    public function handle()
    {
        $response = Http::get('http://api.11st.co.kr/rest/cateservice/category');
        $xmlBody = $response->body();

        // 인코딩 확인 및 변환 (EUC-KR -> UTF-8)
        if (!mb_check_encoding($xmlBody, 'UTF-8')) {
            $xmlBody = iconv("EUC-KR", "UTF-8", $xmlBody);
        }

        try {
            $xml = new SimpleXMLElement($xmlBody);
            $categories = $xml->children('ns2', true)->category;
            $arrayData = $this->xmlToArray($categories);
            $jsonData = json_encode($arrayData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo $jsonData;
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    private function xmlToArray($xml)
    {
        $result = [];

        foreach ($xml as $element => $node) {
            if ($node->children('ns2', true)->count() > 0) {
                $result[$element] = $this->xmlToArray($node->children('ns2', true));
            } else {
                $result[$element] = (string) $node;
            }
        }

        return $result;
    }
}
