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
            // 응답 본문의 인코딩을 EUC-KR에서 UTF-8로 변환
            $euc_kr_body = $response->body();

            // 원본 응답을 로그로 기록
            Log::info('Original Response Body (EUC-KR): ' . $euc_kr_body);

            // UTF-8로 변환 시도
            $utf8_response_body = @iconv('EUC-KR', 'UTF-8//IGNORE', $euc_kr_body);

            // 변환된 응답을 로그로 기록
            Log::info('Converted UTF-8 Response Body: ' . $utf8_response_body);

            // XML 선언의 인코딩을 UTF-8로 변경
            $utf8_response_body = preg_replace('/<\?xml version="1.0" encoding="EUC-KR"\?>/', '<?xml version="1.0" encoding="UTF-8"?>', $utf8_response_body);

            // UTF-8 문자열이 올바른지 확인
            if (mb_detect_encoding($utf8_response_body, 'UTF-8', true) === false) {
                return [
                    'status' => false,
                    'message' => 'UTF-8 변환 실패',
                    'error' => '응답 본문을 UTF-8로 변환하는 과정에서 에러가 발생했습니다.'
                ];
            }

            // 변환된 UTF-8 문자열을 SimpleXMLElement로 파싱
            libxml_use_internal_errors(true); // XML 파싱 오류를 내부적으로 처리하도록 설정
            $xml = simplexml_load_string($utf8_response_body);

            if ($xml === false) {
                // XML 파싱 오류 메시지 수집
                $errors = libxml_get_errors();
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->message;
                }
                libxml_clear_errors();

                return [
                    'status' => false,
                    'message' => 'XML 파싱 실패',
                    'error' => 'XML을 파싱하는 과정에서 에러가 발생했습니다: ' . implode(", ", $errorMessages),
                    'response_body' => $utf8_response_body // 디버깅을 위한 변환된 응답 본문 추가
                ];
            }

            // XML을 배열로 변환
            $xmlArray = json_decode(json_encode($xml), true);

            // JSON 형식으로 변환하여 로그 기록
            $jsonFormattedData = json_encode($xmlArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Log::info('Formatted Response Data: ' . $jsonFormattedData);

            return [
                'status' => true,
                'data' => $xmlArray // 파싱된 XML 데이터를 배열로 반환
            ];
        }

        return [
            'status' => false,
            'message' => 'API 요청을 보내는 과정에서 에러가 발생했습니다.',
            'error' => mb_convert_encoding($response->body(), 'UTF-8', 'EUC-KR')
        ];
    }
}
