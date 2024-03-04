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
        await sign(page, username, password);
        // 몇 페이지까지 반복할 건데? 마지막 페이지잖아. 그럼 마지막 페이지 숫자를 구해오면 총 페이지 숫자겠지?
        const numPages = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPages; i > 0; i--) {
            await moveToPage(page, listURL, i);
            const tmpProducts = await scrapeProducts(page);
            products.push(...tmpProducts);
        }
        // 지정된 URL에서 상품 목록 페이지 처리를 수행합니다.
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
// 총 페이지 숫자를 구하는 함수.
async function getNumPage(page, url) {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    const numPages = await page.evaluate(() => {
        const paginationElements = document.querySelectorAll('body > table > tbody > tr:nth-child(2) > td > table > tbody > tr > td.outline_side > table > tbody > tr > td > table > tbody > tr:nth-child(10) > td a');
        const lastPageElement = paginationElements[paginationElements.length - 1];
        const lastPageText = lastPageElement.textContent.trim();
        const numPages = parseInt(lastPageText.replace(/[^0-9]/g, ''));
        return numPages;
    });
    return numPages;
}
// 사용자 로그인을 처리하는 비동기 함수입니다.
async function sign(page, username, password) {
    // 로그인 페이지로 이동합니다.
    await page.goto('https://www.kapoka.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    // 사용자 이름과 비밀번호 입력 필드에 값을 입력합니다.
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(3) > td:nth-child(2) > input[type=password]', password);
    // 로그인 버튼을 클릭합니다.
    await page.click('#form > table > tbody > tr:nth-child(2) > td[class="noline"]');
    // 페이지 내비게이션이 완료될 때까지 기다립니다.
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}
// 상품 목록 페이지를 처리하는 함수입니다.
async function moveToPage(page, initialURL, curPage) {
    // 현재 페이지의 전체 URL을 생성합니다.
    const fullUrl = initialURL + '&page=' + curPage;
    // 초기 URL로 페이지로 이동합니다. 페이지 로딩이 완료될 때까지 기다립니다.
    await page.goto(fullUrl, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        // 상품 정보를 처리하여 추출하는 함수입니다.
        function processProduct(productElement) {
            try {
                const productNameElement = productElement.querySelector('div:nth-child(2) > a');
                const name = productNameElement.textContent.trim();
                // const nameText = productNameElement.textContent.trim();
                // const regexPattern = /\[[^\]]*\]/g;
                // const name = nameText.replace(regexPattern, '');
                const productPriceText = productElement.querySelector('div:nth-child(3) > b').textContent;
                const price = productPriceText.replace(/[^0-9]/g, '').trim();
                const imageElement = productElement.querySelector('div:nth-child(1) > a > img');
                const image = imageElement.src;
                const href = productNameElement.href;
                const platform = '카포카';

                return { name, price, image, href, platform };
            } catch (error) {
                return false;
            }
        }

        // 품절 상품 이미지를 확인하는 함수입니다.
        function hasSoldOutImage(productElement) {
            return productElement.querySelector('img[src="/shop/data/skin/apple_tree/img/icon/good_icon_soldout.gif"]') !== null;
        }

        // 스크랩된 상품을 저장할 배열입니다.
        const products = [];

        // 상품 요소들을 선택합니다.
        const productElements = document.querySelectorAll('td[align="center"][valign="top"][width="25%"]');

        // 각 상품 요소에 대해 상품 정보를 스크랩합니다.
        productElements.forEach(productElement => {
            // 품절 상품인지 확인하고 아닌 경우 상품 정보를 스크랩합니다.
            if (!hasSoldOutImage(productElement)) {
                const productInfo = processProduct(productElement);
                if (productInfo !== false) {
                    products.push(productInfo);
                }
            }
        });

        return products;
    });

    return products;
}


