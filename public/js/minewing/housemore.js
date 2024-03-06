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
        // await browser.close();
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
        // 상품 정보를 처리하여 추출하는 함수입니다.
        function processProduct(productElement) {
            try {
                const productNameElement = productElement.querySelector('div.description > strong > a > span:nth-child(2)');
                const nameText = productNameElement.textContent.trim();
                const regexPattern = /\([^)]*(판매|품절)[^)]*\)/g;
                const name = nameText.replace(regexPattern, '');
                const productPriceText = productElement.querySelector('div.description > ul > li:nth-child(1) > span:nth-child(2)').textContent;
                const price = productPriceText.replace(/[^0-9]/g, '').trim();
                const imageElement = productElement.querySelector('div.thumbnail > div.prdImg');
                const image = imageElement.src;
                const href = productNameElement.href;
                const platform = '하우스모어';

                return { name, price, image, href, platform };
            } catch (error) {
                return false;
            }
        }

        function hasSoldOutImage(productElement) {
            return productElement.querySelector('div.thumbnail > div.icon > div.promotion > img') !== null;
        }

        const products = [];

        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal.ec-base-product > ul > li');

        productElements.forEach(productElement => {
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




