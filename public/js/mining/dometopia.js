const puppeteer = require('puppeteer');

async function login(page, username, password) {
    await page.goto('https://dometopia.com/member/login', { waitUntil: 'networkidle2', timeout: 0 });
    await page.type('#userid', username);
    await page.type('#password', password);
    await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
    await page.waitForNavigation();
}

async function getProductList(page) {
    return page.evaluate(() => {
        const productSelector = 'td[valign="top"]';
        const baseURL = 'https://dometopia.com';
        const products = [];

        const isValidProduct = productElement => {
            const tagElements = productElement.querySelectorAll('dl > dd.goodsDisplayCode > table > tbody > tr:nth-child(1) > td > a:nth-child(1) img');
            const singleTagSRC = '/data/skin/beauty/images/icon/G.gif';
            const importTagSRC = '/data/skin/beauty/images/icon/__H.gif';

            const hasTag = (src) => Array.from(tagElements).some(tagElement => tagElement.getAttribute('src') === src);

            const singleTagExists = hasTag(singleTagSRC);
            const importTagExists = hasTag(importTagSRC);

            return singleTagExists && !importTagExists;
        };

        const extractProductDetails = productElement => {
            const nameSelector = 'dl > dd.goodsDisplayTitle > div > a > h6';
            const priceSelector = 'dl > dd.goodsDisplaySalePrice.clfix > div > table > tbody > tr > td.price_num';
            const imageSelector = 'dl > dt > span > a > img';
            const hrefSelector = 'dl > dd.goodsDisplayTitle > div > a';

            const name = productElement.querySelector(nameSelector)?.textContent.trim();
            const productPriceText = productElement.querySelector(priceSelector)?.textContent;
            const image = productElement.querySelector(imageSelector)?.getAttribute('src');
            const productHref = productElement.querySelector(hrefSelector)?.getAttribute('href');

            if (name && !name.includes('해외직구') && !name.includes('해외배송')) {
                const price = parseInt(productPriceText.replace(/[^0-9]/g, '').trim());
                const href = baseURL + productHref;
                const platform = '도매토피아';

                return { name, price, image, href, platform };
            }
        };

        document.querySelectorAll(productSelector).forEach(productElement => {
            if (isValidProduct(productElement)) {
                const productDetails = extractProductDetails(productElement);
                if (productDetails) {
                    products.push(productDetails);
                }
            }
        });

        return products;
    });
}

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [listURL, username, password, curPage] = process.argv.slice(2);
        await login(page, username, password);

        const fullURL = `${listURL}&perpage=150&page=${curPage}`;
        await page.goto(fullURL, { waitUntil: 'networkidle2', timeout: 0 });

        const products = await getProductList(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
