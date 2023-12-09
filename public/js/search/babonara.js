const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const keyword = process.argv[2];
        // 웹 페이지로 이동
        await page.goto('http://babonara.co.kr/shop/member/login.php');

        await page.waitForSelector('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]');
        await page.waitForSelector('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]');
        await page.waitForSelector('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');

        // 사용자명과 비밀번호 입력
        await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', 'sungiltradekorea');
        await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', 'tjddlf88!@');

        // 로그인 버튼 클릭
        await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');

        // 새로운 키워드 입력
        await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > div > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td:nth-child(3) > form > table > tbody > tr > td:nth-child(1) > input');
        await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > div > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td:nth-child(3) > form > table > tbody > tr > td:nth-child(2) > input[type=image]');
        await page.type(
            'body > table > tbody > tr:nth-child(1) > td > div > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td:nth-child(3) > form > table > tbody > tr > td:nth-child(1) > input',
            keyword
        );

        // 검색 버튼 클릭
        await page.click(
            'body > table > tbody > tr:nth-child(1) > td > div > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td:nth-child(3) > form > table > tbody > tr > td:nth-child(2) > input[type=image]'
        );

        // 검색 결과 페이지 완전히 로딩될 때까지 대기

        async function wait(seconds) {
            return new Promise(resolve => setTimeout(resolve, seconds * 1000));
        }

        // 5초 동안 기다린 후 코드를 진행
        await wait(5);
        // 이후에 원하는 코드를 작성


        await page.waitForSelector('#form > table > tbody');

        // 상품 정보 추출
        const products = await page.evaluate(() => {
            const productElements = document.querySelectorAll('td[align="center"][valign="top"][width="25%"]');
            const productsArr = [];

            for (const productElement of productElements) {
                const nameHref = productElement.querySelector('div[style="padding:5"] > a');
                const name = nameHref.textContent;

                // URL 정규화를 위한 기본 URL
                const baseUrl = 'http://babonara.co.kr/shop/goods/';

                // 상품 링크 URL 정규화
                const originUrl = nameHref.getAttribute('href');
                const href = new URL(originUrl, baseUrl).href;

                // 숫자만 추출하는 정규식
                const priceText = productElement.querySelector('div[style="padding-bottom:3px"] > b').textContent;
                const priceMatches = priceText.match(/\d+/g);
                const price = priceMatches ? parseInt(priceMatches.join('')) : 0;

                // 상품 이미지 URL 정규화
                const imageHref = productElement.querySelector('div > a > img').getAttribute('src');
                const image = new URL(imageHref, baseUrl).href;

                const platform = '바보나라';
                productsArr.push({ name, price, href, image, platform });
            }
            return productsArr;
        });
        // 상품 정보 출력
        console.log(JSON.stringify(products));
    } catch (error) {
        console.log(JSON.stringify(error));
    } finally {
        await browser.close();
    }
})();
