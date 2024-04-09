const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const product = await scrapeProduct(page, url);
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
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
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
    await page.goto('https://www.sellerfriend.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.member_login_box > div.login_input_sec > button');
    await page.waitForNavigation();
}


async function scrapeProduct(page, productHref) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const productName = document.querySelector('#frmView > div > div > div.item_detail_tit > h3').textContent.trim();
        const productPrice = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd > strong > strong').textContent.trim().replace(/[^\d]/g, '');

        const productImage = document.querySelector('#contents > div > div.content_box > div.item_photo_info_sec > div > div > div.item_photo_slide > ul > div > div > li > a > img').getAttribute('src').trim();
        if (!productImage) {
            return false;
        }

        const images = document.querySelectorAll('#detail > div.detail_cont > div:nth-child(2) > div.txt-manual > p img');

        const productDetailImageElement = [];
        images.forEach((image) => {
            const imageUrl = image.getAttribute('src').trim();
            productDetailImageElement.push(imageUrl);
        });
        const productDetail = productDetailImageElement;
        let hasOption = false;
        let productOptions = [];
        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 48
        };
    }, productHref);
    return product;
}
