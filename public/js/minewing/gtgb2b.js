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
    await page.goto('https://gtgb2b.com/member/login.php', { waitUntil: 'networkidle0' });
    // 사용자 이름과 비밀번호 입력 필드에 값을 입력합니다.
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    // 로그인 버튼을 클릭합니다.
    await page.click('#formLogin > div.login > button');
    // 페이지 내비게이션이 완료될 때까지 기다립니다.
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

//상품 목록 페이지를 처리하는 함수입니다.
async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
    // 상품 개수를 페이지에서 추출합니다.
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#content > div > div > div.cg-main > div.goods-list > span > strong').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    // 상품 개수를 기반으로 최종 URL을 구성합니다.
    listURL += '&sort=&pageNum=' + numProducts;
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
}

// 상품 정보를 스크래핑하는 함수입니다.
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('#content > div > div > div.cg-main > div.goods-list > div > div > ul li');
        const products = [];

        function processProduct(productElement) {
            const productNameElement = productElement.querySelector('li > div > div.txt > a > strong');
            const name = productNameElement.textContent.trim();
            const productPriceText = productElement.querySelector('li > div > div.price.gd-default > span > strong').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const imageElement = productElement.querySelector('li > div > div.thumbnail > a > img');
            const image = imageElement.src;
            const href = productElement.querySelector('li > div > div.txt > a').href.trim();
            const platform = 'GT생활건강';

            return { name, price, image, href, platform };
        }

        function hasStockMethod(productElement) {
            const soldOutImage = productElement.querySelector('li > div > div.txt > div > img');
            return !soldOutImage;
        }

        for (const productElement of productElements) {
            const hasStockMethodResult = hasStockMethod(productElement);
            if (hasStockMethodResult === false) {
                continue; // 품절된 제품을 건너뜁니다.
            }
            try {
                const productInfo = processProduct(productElement);
                products.push(productInfo);
            } catch (error) {
                console.error("Error processing product: ", error);
                continue; // 에러가 발생한 제품을 건너뜁니다.
            }
        }

        return products;
    });

    return products;
}

