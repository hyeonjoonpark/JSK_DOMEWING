const puppeteer = require('puppeteer');
const url = require('url');
const querystring = require('querystring');
// 상품 정보를 추출하는 비동기 함수
async function extractProductsOnPage(page) {
    return await page.evaluate(() => {
        const productElements = document.querySelectorAll('td[align="center"][valign="top"][width="25%"]');
        const baseUrl = 'http://babonara.co.kr/shop/goods/';
        const productsArr = [];
        for (const productElement of productElements) {
            const soldOutElement = productElement.querySelector('div[style="padding:3px"] > img');
            if (soldOutElement) {
                continue;
            }
            const nameHref = productElement.querySelector('div[style="padding:5"] > a');
            const name = nameHref.textContent.trim();
            // 숫자만 추출하는 정규식
            const priceText = productElement.querySelector('div[style="padding-bottom:3px"] > b').textContent;
            const priceMatches = priceText.match(/\d+/g);
            const price = priceMatches ? parseInt(priceMatches.join('')) : 0;
            // 상품 이미지 URL 정규화
            const imageHref = productElement.querySelector('div > a > img').getAttribute('src');
            const image = new URL(imageHref, baseUrl).href;
            // 상품 링크 URL 정규화
            const originUrl = nameHref.getAttribute('href');
            const href = new URL(originUrl, baseUrl).href;
            const platform = '바보나라';
            productsArr.push({ name, price, image, href, platform });
        }
        return productsArr;
    });
}
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    // page.setDefaultNavigationTimeout(0);
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password, curPage] = args;
        const parsedURL = new URL(listURL);
        const params = querystring.parse(parsedURL.search.substring(1));
        const filteredParams = {
            category: params.category // 'category'만 남깁니다.
        };
        parsedURL.search = querystring.stringify(filteredParams);
        const processedURL = parsedURL.toString() + '&sort=goods_link.sort&page_num=48';
        let pageURL = '';
        if (curPage != 1 && curPage != '1') {
            pageURL = '&page=' + curPage;
        }
        const fullURL = processedURL + pageURL;
        await page.goto('http://babonara.co.kr/shop/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('input[name="m_id"]', username);
        await page.type('input[name="password"]', password);
        await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
        await page.waitForNavigation();
        // 웹 페이지로 이동
        await page.goto(fullURL, { waitUntil: 'load' });
        // await new Promise((page) => setTimeout(page, 5000));
        const allProducts = await extractProductsOnPage(page);
        // 상품 정보 출력
        console.log(JSON.stringify(allProducts));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
