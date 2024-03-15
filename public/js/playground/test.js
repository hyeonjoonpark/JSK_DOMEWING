const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

        await login(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            if (product !== false) {
                products.push(product);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://gtgb2b.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await scrapeProductPrice(page);
        const hasOption = await getHasOption(page);
        let productOptions = [];
        if (hasOption === true) {
            productOptions = await getProductOptions(page);
        }
        await page.evaluate(() => {
            const productImageThumbElements = document.querySelectorAll('#content > div.goods-view > div.goods > div > div.more-thumbnail > div.slide > div > div > div a');
            productImageThumbElements[productImageThumbElements.length - 1].click();
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        const product = await page.evaluate((productHref, productOptions, hasOption, productPrice) => {
            const productNameElement = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2');
            const productName = productNameElement.textContent.trim();
            const productImage = document.querySelector('#mainImage > img').src;
            const productDetailElements = document.querySelectorAll('#detail > div.txt-manual > div:nth-child(12) > img');
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const productDetailImage = productDetailElement.src;
                productDetail.push(productDetailImage);
            }
            return {
                productName,
                productPrice,
                productImage,
                productDetail,
                hasOption,
                productOptions,
                productHref,
                sellerID: 29
            };
        }, productHref, productOptions, hasOption, productPrice);
        return product;
    } catch (error) {
        return false;
    }
}

async function scrapeProductPrice(page) {
    const productPriceText = await page.evaluate(() => {
        const priceElement = document.querySelector('#frmView > div > div.item > ul > li.price > div > strong');
        return priceElement ? priceElement.textContent.trim() : '';
    });
    return parseInt(productPriceText.replace(/[^\d]/g, ''), 10);
}

async function getHasOption(page) {
    // 상품 옵션 존재 여부 확인 로직
}

async function getProductOptions(page) {
    // 상품 옵션 스크랩 로직
}
