<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class TestController extends Controller
{
    public function handle(Request $request)
    {
        $url = 'https://domesin.com/scm/login.html';

        $client = new Client();
        $crawler = $client->request('GET', $url);

        // 사용자 이름(ID) 필드에 값을 입력
        $idField = $crawler->filter('input[name="m_id"]')->first();
        $idField->setValue('sungiltradekorea');

        // 비밀번호(PW) 필드에 값을 입력
        $pwField = $crawler->filter('input[name="m_pw"]')->first();
        $pwField->setValue('tjddlf88!@');

        // 로그인 버튼을 클릭
        $loginButton = $crawler->filter('input[type="image"]')->first();
        $form = $loginButton->form();
        $client->submit($form);

        // 로그인 후 현재 페이지 내용을 가져옵니다
        $content = $client->getResponse()->getContent();

        return view('logged_in_page', ['content' => $content]);
    }
}
