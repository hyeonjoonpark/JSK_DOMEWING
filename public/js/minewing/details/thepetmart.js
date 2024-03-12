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
    await page.goto('https://thepetmart.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > div.login_btn');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {//해외배송가능상품
        const productName = document.querySelector('#container > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h3').textContent.trim();
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#container > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > img').getAttribute('src').trim();
        //#prdDetail > div > div:nth-child(13) > img

        // const images = document.querySelectorAll('#prdDetail > div img'); 이미지는 설명 들어야함
        // const productDetailImageElement = [];
        // images.forEach((image) => {
        //     const imageUrl = image.getAttribute('src').trim();
        //     productDetailImageElement.push(imageUrl);
        // });
        // const productDetail = productDetailImageElement.length > 0 ? productDetailImageElement : 'productDetailImage not found';
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
            sellerID: 28
        };
    }, productHref);
    return product;
}
