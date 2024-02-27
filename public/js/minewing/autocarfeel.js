const puppeteer = require('puppeteer');

// 비동기 함수를 사용하여 스크립트의 주 실행 흐름을 정의합니다.
(async () => {
    // Puppeteer를 사용하여 브라우저 인스턴스를 생성합니다. headless 모드를 false로 설정하여 GUI를 사용합니다.
    const browser = await puppeteer.launch({ headless: false });
    // 새 탭 페이지를 엽니다.
    const page = await browser.newPage();
    // 커맨드 라인에서 입력된 인자들을 배열로 가져옵니다. 첫 두 인자는 무시합니다.
    const [listURL, username, password] = process.argv.slice(2);

    try {
        // 로그인 함수를 호출하여 사용자 인증을 수행합니다.
        await login(page, username, password);
        // 지정된 URL에서 상품 목록 페이지 처리를 수행합니다.
        await processPage(page, listURL);
        // 상품 데이터를 스크래핑합니다.
        const products = await scrapeProducts(page);
        // 스크래핑된 상품 데이터를 JSON 형식으로 콘솔에 출력합니다.
        console.log(JSON.stringify(products));
    } catch (error) {
        // 오류가 발생한 경우, 오류 메시지를 콘솔에 출력합니다.
        console.error(error);
    } finally {
        // 작업이 완료되면 브라우저를 닫습니다.
        await browser.close();
    }
})();

// 사용자 로그인을 처리하는 비동기 함수입니다.
async function login(page, username, password) {
    // 로그인 페이지로 이동합니다.
    await page.goto('http://autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    // 사용자 이름과 비밀번호 입력 필드에 값을 입력합니다.
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    // 로그인 버튼을 클릭합니다.
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    // 페이지 내비게이션이 완료될 때까지 기다립니다.
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

// 상품 목록 페이지를 처리하는 함수입니다.
async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
    // 상품 개수를 페이지에서 추출합니다.
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#b_white > font > b').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    // 상품 개수를 기반으로 최종 URL을 구성합니다.
    listURL += '&page_num=' + numProducts;
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
}

// 상품 정보를 스크래핑하는 함수입니다.
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        // 상품 정보를 처리하여 추출하는 함수입니다.
        function processProduct(productElement) {
            const productNameElement = productElement.querySelector('div:nth-child(2) > a');
            productNameElement.querySelectorAll('font[color="red"]').forEach(el => el.remove());
            const name = productNameElement.textContent.trim();
            const productPriceText = productElement.querySelector('div:nth-child(3) > b').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();

            const toAbsoluteUrl = (src, baseUrl) => new URL(src.replace(/^\.\.\//, ''), baseUrl).href;

            const baseUrl = 'http://autocarfeel.co.kr/shop/data';
            const imageSrc = productElement.querySelector('div:nth-child(1) > a > img').getAttribute('src');
            const image = toAbsoluteUrl(imageSrc, baseUrl);
            const href = productElement.querySelector('div:nth-child(1) > a').href;
            const platform = '오토카필';

            return { name, price, image, href, platform };
        }

        // 상품이 품절 상태인지 확인하는 함수입니다.
        function hasStockMethod(productElement) {
            return !productElement.textContent.includes('품절상품입니다.');
        }

        const productElements = document.querySelectorAll('td[align="center"][valign="top"][width="25%"]');
        const products = [];
        productElements.forEach(productElement => {
            if (hasStockMethod(productElement)) {
                const productInfo = processProduct(productElement);
                products.push(productInfo);
            }
        });

        return products;
    });

    return products;
}
