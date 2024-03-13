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
        const numProductsText = document.querySelector('body > div.container.g_skin_list > div.mt-3.mt-lg-5.mb-lg-3 > form > div.bg-light.bg-none-mobile.row.align-items-center.justify-content-between.px-2.px-lg-3.bg-light.bg-none-mobile.border-top.border-top-dark.position-relative > div.col-12.col-sm.text-truncate.text-dark.text-center.text-sm-left.py-sm-3.py-2.sch-pd > span').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 100;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    listUrl += '&ps_page=' + curPage + '&ps_limit=5';
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        // 판매 또는 품절 상태인 상품을 확인하는 함수
        function filterProduct(productElement) {
            const tagList = productElement.querySelectorAll('div.col-lg-28w.col-10.py-lg-1 > div > div > div > div.col-lg-12.text-left > p:nth-child(2) span');
            for (const tag of tagList) {
                const tagText = tag.textContent.trim();
                if (tagText.includes('배송불가') || tagText.includes('안함') || tagText.includes('품절') || tagText.includes('미정') || tagText.includes('단종') || tagText.includes('반품불가')) {
                    return false;
                }
            }
            return true;
        }

        // 상품 정보를 처리하여 추출하는 함수
        function processProduct(productElement) {
            try {
                const productNameElement = productElement.querySelector('div.col-lg-28w.col-10.py-lg-1 > div > div > div > div.col-lg-12.text-left > p:nth-child(1)');
                let name = productNameElement.textContent.trim();

                const productPriceText = productElement.querySelector('div.col-lg.col-12 > div > div > div > div:nth-child(1) > div:nth-child(4) > div > span > span').textContent;
                const price = productPriceText.replace(/[^0-9]/g, '').trim();

                const imageElement = productElement.querySelector('div.col-lg-1.col-2 > div > div > a > img');
                const image = imageElement.src;

                // href 값을 추출하는 부분을 수정
                const hrefElement = productElement.querySelector('div.col-lg-28w.col-10.py-lg-1 > div > div > div > div.col-lg-12.text-left > p:nth-child(1) > a')
                const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';

                const platform = '하우스모어';

                // 정의된 href 값을 반환 객체에 포함
                return { name, price, image, href, platform };
            } catch (error) {
                console.error('Error processing product:', error);
                return null;
            }
        }


        const productElements = document.querySelectorAll('#data-style > div div');
        const processedProducts = [];

        productElements.forEach(productElement => {
            if (filterProduct(productElement) === true) {
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


