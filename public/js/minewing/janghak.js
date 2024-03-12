const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, listURL, i);
            let list = await scrapeProducts(page);
            products.push(...list);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();


async function signIn(page, username, password) {

    await page.goto('https://www.jhmungu.com/shop/login.php', { waitUntil: 'networkidle0' });
    await page.type('div > div:nth-child(1) > form > div:nth-child(2) > input[type=text]:nth-child(1)', username);
    await page.type('div > div:nth-child(1) > form > div:nth-child(2) > input.mt-2', password);
    await page.click('div > div:nth-child(1) > form > div.form-group.row.text-center > div.col-12.col-md > button');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('body > div.container.g_skin_list > div.d-none.d-md-block.position-relative.text-center.g_list_category_depth1 > div > span.font-weight-bold.text-thema').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 80;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    listUrl += '&page=' + curPage;
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        // 특정 셀렉터의 텍스트에 제외할 문자열들 중 하나라도 포함되어 있는지 확인하는 함수
        function containsExcludedText(productElement, selector, excludedTexts) {
            const element = productElement.querySelector(selector);
            if (element) {
                const textContent = element.textContent;
                return excludedTexts.some(excludedText => textContent.includes(excludedText));
            }
            return false;
        }

        // 상품 정보를 처리하여 추출하는 함수
        function processProduct(productElement) {
            // 검사하고자 하는 셀렉터
            const checkSelector = 'div.col-lg-28w.col-10.py-lg-1 > div > div > div > div:nth-child(2) > p:nth-child(2) > span';
            // 제외하고자 하는 텍스트 목록
            const excludedTexts = ["택배배송불가", "취급안함", "일시품절", "입고미정", "단종"];

            // 해당 셀렉터의 텍스트에 제외할 문자열이 포함되어 있는지 확인
            if (containsExcludedText(productElement, checkSelector, excludedTexts)) {
                // 조건을 만족하면, 이 상품은 결과 목록에서 제외
                return null;
            }

            try {
                // 상품 정보 추출 로직
                const productNameElement = productElement.querySelector('div.col-lg-28w.col-10.py-lg-1 > div > div > div > div.col-lg-12.text-left > p:nth-child(1)');
                let name = productNameElement.textContent.trim();

                const productPriceText = productElement.querySelector('div.col-lg.col-12 > div > div > div > div:nth-child(1) > div:nth-child(4) > div > span > span').textContent;
                const price = productPriceText.replace(/[^0-9]/g, '').trim();

                const imageElement = productElement.querySelector('div.col-lg-1.col-2 > div > div > a > img');
                const image = imageElement.src;

                const hrefElement = productElement.querySelector('div.col-lg-28w.col-10.py-lg-1 > div > div > div > div.col-lg-12.text-left > p:nth-child(1) > a');
                const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';

                const platform = '장학문구사';

                return { name, price, image, href, platform };
            } catch (error) {
                console.error('Error processing product:', error);
                return null;
            }
        }

        // 상품 요소들을 순회하며 정보 추출
        const productElements = document.querySelectorAll('#data-style > div div');
        const processedProducts = [];

        productElements.forEach(productElement => {
            const productInfo = processProduct(productElement);
            if (productInfo) {
                processedProducts.push(productInfo);
            }
        });

        return processedProducts;
    });

    return products; // 스크레이핑된 상품 정보 반환
}




