const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
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
    await page.goto('https://www.tckjong.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#login_id', username);
    await page.type('#login_pwd', password);
    await page.click('#login > div.inner > div.login_form > form > span > input[type=submit]');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const productName = document.querySelector('#detail > form > div > div.list_btn > h3').textContent.trim();
        const productPrice = document.querySelector('#sell_prc_str').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#mainImg').getAttribute('src').trim();
        const images = document.querySelectorAll('#detail > div > div.detail_info img');
        const productDetailImageElement = [];
        images.forEach((image) => {
            const imageUrl = image.getAttribute('src').trim();
            productDetailImageElement.push(imageUrl);
        });
        const productDetail = productDetailImageElement.length > 0 ? productDetailImageElement : 'productDetailImage not found';
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
            sellerID: 26
        };
    }, productHref);
    return product;
}
