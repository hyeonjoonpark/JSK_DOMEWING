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
    await page.goto('https://www.domecall.net/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const productName = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2').textContent.trim();
        const productPrice = document.querySelector('#frmView > div > div.item > ul > li.price > div > strong').textContent.trim().replace(/[^\d]/g, '');

        const productImage = getProductImage();
        if (!productImage) {
            return false;
        }

        const images = document.querySelectorAll('#detail > div.txt-manual img');
        const productDetailImageElement = [];
        images.forEach((image) => {
            const imageUrl = image.getAttribute('src').trim();
            if (!imageUrl.includes('warning')) {
                productDetailImageElement.push(imageUrl);
            }
        });
        const productDetail = productDetailImageElement.length > 0 ? productDetailImageElement : 'productDetailImage not found';
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
            sellerID: 23
        };
        function getProductImage() {
            const selectors = [
                '#content > div.goods-view > div.goods > div > div.more-thumbnail > div.slide > div > div > div > span:nth-child(4) > a > img',
                '#content > div.goods-view > div.goods > div > div.more-thumbnail > div.slide > div > div > div > span.swiper-slide.slick-slide.slick-current.slick-active > a > img'
            ];
            for (const selector of selectors) {
                const imgElement = document.querySelector(selector);
                if (imgElement) {
                    return imgElement.getAttribute('src').trim();
                }
            }
            return null;
        }
    }, productHref);
    return product;
}
