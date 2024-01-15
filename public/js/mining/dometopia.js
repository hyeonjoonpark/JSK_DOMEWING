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
            const soldOutElement = document.querySelector('dl > dd.goodsDisplayCode > table > tbody > tr:nth-child(1) > td > a:nth-child(2) > img');
            let soldOutTagExists = false;
            if (soldOutElement) {
                soldOutTagExists = true;
            }
            const tagElements = Array.from(productElement.querySelectorAll('dl > dd.goodsDisplayCode > table > tbody > tr:nth-child(1) > td > a:nth-child(1) img'));
            const singleTagSRC = '/data/skin/beauty/images/icon/G.gif';
            const importTagSRC = '/data/skin/beauty/images/icon/__H.gif';
            const hasTag = src => tagElements.some(tagElement => tagElement.getAttribute('src') === src);
            const singleTagExists = hasTag(singleTagSRC);
            const importTagExists = hasTag(importTagSRC);
            return singleTagExists && !importTagExists && !soldOutTagExists;
        };
        const productElements = document.querySelectorAll(productSelector);
        for (const productElement of productElements) {
            if (!isValidProduct(productElement)) {
                continue;
            }
            const nameSelector = 'dl > dd.goodsDisplayTitle > div > a > h6';
            const priceSelector = 'dl > dd.goodsDisplaySalePrice.clfix > div > table > tbody > tr > td.price_num';
            const imageSelector = 'dl > dt > span > a > img';
            const hrefSelector = 'dl > dd.goodsDisplayTitle > div > a';
            const nameElement = productElement.querySelector(nameSelector);
            const priceElement = productElement.querySelector(priceSelector);
            const imageElement = productElement.querySelector(imageSelector);
            const hrefElement = productElement.querySelector(hrefSelector);
            if (!nameElement || !priceElement || !imageElement || !hrefElement) {
                continue; // 필요한 요소 중 하나라도 없으면, 이 상품은 건너뜀
            }
            const name = nameElement.textContent.trim();
            const productPriceText = priceElement.textContent;
            const image = imageElement.getAttribute('src');
            const productHref = hrefElement.getAttribute('href');
            if (name.includes('해외직구') || name.includes('해외배송')) {
                continue; // 해외직구 또는 해외배송 상품은 건너뜀
            }
            const price = parseInt(productPriceText.replace(/[^0-9]/g, '').trim());
            const href = baseURL + productHref;
            const platform = '도매토피아';
            products.push({ name, price, image, href, platform });
        }
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
