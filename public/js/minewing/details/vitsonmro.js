const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const signInResult = await signIn(page, username, password);
        if (signInResult === false) {
            console.log(false);
            return;
        }
        const products = [];
        for (const url of urls) {
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const product = await scrapeProduct(page, url);
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
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
async function signIn(page, username, password) {
    const navigateWithRetryResponse = await navigateWithRetry(page, 'https://vitsonmro.com/mro/login.do');
    if (navigateWithRetryResponse === false) {
        return false;
    }
    await page.type('#custId', username);
    await page.type('#custPw', password);
    await page.click('#loginForm > div > a:nth-child(3)');
    await page.waitForNavigation();
    return true;
}
async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {
        let productName = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.top_title_bar > h3').textContent.trim();
        const productStandard = document.querySelector('#table > tbody > tr:nth-child(2) > td:nth-child(2)').textContent.trim();
        productName += ' ' + productStandard;
        const productPrice = document.querySelector('#negoPrice').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.deal_view > div.deal_gallery > div.swiper-container.gallery-top.swiper-container-horizontal > div > div.swiper-slide.swiper-slide-active > img').src;
        const images = document.querySelectorAll('#detail_box > div > ul img');
        const productDetail = Array.from(images, img => {
            let src = img.getAttribute('src');
            return src;
        });
        const hasOption = false;
        const productOptions = [];
        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 13
        };
    }, productHref);
    return product;
}