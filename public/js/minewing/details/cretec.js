const puppeteer = require('puppeteer');
const fs = require('fs');
const delay = (time) => new Promise(resolve => setTimeout(resolve, time));
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page);
        const products = [];
        for (const url of urls) {
            const navigateWithRetryResult = await navigateWithRetry(page, url.productHref);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const product = await scrapeProduct(page, url.id);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page) {
    await page.goto('https://ctx.cretec.kr/CtxApp/index.do', { waitUntil: 'networkidle0' });
    await page.type('#ctxId', '17572326');
    await page.type('#password', 'Tjddlf88!@');
    await page.click('#ctxUserVO > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) {
        try {
            await page.goto(url, { waitUntil: 'networkidle0' });
            return true;
        } catch (error) {
            if (i < attempts - 1) {
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    return false;
}
async function scrapeProduct(page, productId) {
    return await page.evaluate((productId) => {
        const shippingFeeElement = document.querySelector('#Container > div.contents > div.detail_body > div.detail_info > div > div.level_1 > dl:nth-child(8) > dd');
        if (!shippingFeeElement) {
            return false;
        }
        const shippingFee = shippingFeeElement.textContent.trim().replace(/[^\d]/g, '');
        if (shippingFee === '') {
            return false;
        }
        const productNameElement = document.querySelector('#Container > div.contents > div.detail_body > div.detail_info > h3');
        if (!productNameElement) {
            return false;
        }
        const productName = productNameElement.textContent.trim();
        const productPriceElement = document.querySelector('#Container > div.contents > div.detail_body > div.detail_info > div > div.level_1 > dl:nth-child(4) > dd > strong');
        if (!productPriceElement) {
            return false;
        }
        const productPrice = productNameElement.textContent.trim().replace(/[^\d]/g, '');
        const productDetail = document.querySelector('#Container > div.contents > div.detail_body > div.detail_info > div > div.level_1 > div.level_2 > table');
        return {
            productId,
            productName,
            productPrice,
            isActive: 'Y',
            productDetail
        };
    }, productId);
}
