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

    await page.goto('https://housemore.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-normalmenu > div.function > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 40;
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
        // 판매 또는 품절 상태인 상품을 확인하는 함수
        function hasSoldOutImage(productElement) {
            return productElement.querySelector('div.thumbnail > div.icon > div.promotion > img') !== null;
        }
        function hasForbiddenWords(name) {
            const forbiddenWords = ['판매금지', '준수'];
            return forbiddenWords.some(word => name.includes(word));
        }


        // 상품 정보를 처리하여 추출하는 함수
        function processProduct(productElement) {
            try {
                const productNameElement = productElement.querySelector('div.description > strong > a > span:nth-child(2)');
                let name = productNameElement.textContent.trim();
                if (name.includes('판매금지')) {
                    return null;
                }

                // 상품명에 "판매금지"나 "준수"가 포함되어 있다면 해당 상품을 건너뜀
                if (hasForbiddenWords(name)) {
                    return null;
                }
                const productPriceText = productElement.querySelector('div.description > ul > li:nth-child(1) > span:nth-child(2)').textContent;
                const price = productPriceText.replace(/[^0-9]/g, '').trim();

                const imageElement = productElement.querySelector('div.thumbnail > div.prdImg > a > img');
                const image = imageElement.src;

                // href 값을 추출하는 부분을 수정
                const hrefElement = productElement.querySelector('div.description > strong > a')
                const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';

                const platform = '하우스모어';

                // 정의된 href 값을 반환 객체에 포함
                return { name, price, image, href, platform };
            } catch (error) {
                console.error('Error processing product:', error);
                return null;
            }
        }


        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal > ul > li');
        const processedProducts = [];

        productElements.forEach(productElement => {
            if (!hasSoldOutImage(productElement)) {
                const productInfo = processProduct(productElement);
                if (productInfo) {
                    processedProducts.push(productInfo);
                }
            }
        });

        return processedProducts;
    });

    return products; // 스크레이핑된 상품 정보 반환
}


