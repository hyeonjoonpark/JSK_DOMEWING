<?php

namespace App\Http\Controllers\OpenMarkets\St11;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ApiController extends Controller
{
    public function builder($apiKey, $method, $url, $data = '')
    {
        $response = Http::withHeaders([
            'Content-type' => 'text/xml;charset=EUC-KR',
            'openapikey' => $apiKey,
        ])->withBody(iconv('UTF-8', 'EUC-KR', $data), 'text/xml')
            ->$method($url);
        if ($response->successful()) {
            $xml = simplexml_load_string($response->body());
            return [
                'status' => true,
                'data' => $xml
            ];
        }
        return [
            'status' => false,
            'message' => '11번가 API 요청을 보내는 과정에서 에러가 발생했습니다.',
            'error' => mb_convert_encoding($response->body(), 'UTF-8', 'EUC-KR')
        ];
    }
    public function orderBuilder($apiKey, $method, $url, $data = '')
    {
        $response = Http::withHeaders([
            'Content-type' => 'text/xml;charset=EUC-KR',
            'openapikey' => $apiKey,
        ])->withBody(iconv('UTF-8', 'EUC-KR', $data), 'text/xml')
            ->$method($url);
        if ($response->successful()) {
            $body = $response->body();
            if ($this->isValidXml($body)) {
                $dom = new \DOMDocument();
                $dom->loadXML($body);
                if ($dom === false) {
                    return [
                        'status' => false,
                        'message' => 'XML 파싱 오류가 발생했습니다.',
                        'error' => libxml_get_errors()
                    ];
                }
                $data = $this->domDocumentToArray($dom);
                return [
                    'status' => true,
                    'data' => $data
                ];
            } else {
                return [
                    'status' => false,
                    'message' => '응답 본문이 유효한 XML 형식이 아닙니다.',
                    'error' => mb_convert_encoding($body, 'UTF-8', 'EUC-KR')
                ];
            }
        }
        return [
            'status' => false,
            'message' => '11번가 API 요청을 보내는 과정에서 에러가 발생했습니다.',
            'error' => mb_convert_encoding($response->body(), 'UTF-8', 'EUC-KR')
        ];
    }
    private function isValidXml($content)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            libxml_clear_errors();
            return false;
        }
        return true;
    }
    private function domDocumentToArray($dom)
    {
        $root = $dom->documentElement;
        return $this->elementToArray($root);
    }
    private function elementToArray($element)
    {
        $result = [];
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attribute) {
                $result['@attributes'][$attribute->name] = $attribute->value;
            }
        }
        if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $childNode) {
                if ($childNode->nodeType === XML_TEXT_NODE) {
                    $result = $childNode->nodeValue;
                } elseif ($childNode->nodeType === XML_ELEMENT_NODE) {
                    $childArray = $this->elementToArray($childNode);
                    $childName = $childNode->nodeName;
                    if (isset($result[$childName])) {
                        if (!is_array($result[$childName]) || !isset($result[$childName][0])) {
                            $result[$childName] = [$result[$childName]];
                        }
                        $result[$childName][] = $childArray;
                    } else {
                        $result[$childName] = $childArray;
                    }
                }
            }
        }
        return $result;
    }
}
