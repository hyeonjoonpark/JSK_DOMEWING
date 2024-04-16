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
    await page.goto('http://www.idosdome.com/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    await page.waitForNavigation();
}


async function scrapeProduct(page, productHref) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const productName = document.querySelector('#goods_spec > form > div:nth-child(4) > b').textContent.trim();
        const productPrice = document.querySelector('#price').textContent.trim().replace(/[^\d]/g, '');

        const productImage = document.querySelector('#objImg').getAttribute('src').trim();
        if (!productImage) {
            return false;
        }

        const images = document.querySelectorAll('#contents > table > tbody > tr > td > p img');

        const productDetailImageElement = [];
        images.forEach((image) => {
            const imageUrl = image.getAttribute('src').trim();
            if (imageUrl !== 'http://ai.esmplus.com/idoscomp/idos/msg.jpg' && imageUrl !== 'http://ai.esmplus.com/idoscomp/idos/good_deliveryInfo.jpg') {
                productDetailImageElement.push(imageUrl);
            }
        });
        const productDetail = productDetailImageElement;
        const optionElement = document.querySelector('#goods_spec > form > div:nth-child(5) > table:nth-child(3) > tbody > tr:nth-child(2) > td > div > select');
        let hasOption = false;
        let productOptions = [];
        if (optionElement) {
            hasOption = true;
            const optionElements = document.querySelectorAll('#goods_spec > form > div:nth-child(5) > table:nth-child(3) > tbody > tr:nth-child(2) > td > div > select option');
            for (let i = 1; i < optionElements.length; i++) {
                const optionElement = optionElements[i];
                const optionText = optionElement.textContent.trim();
                let optionName, optionPrice;
                if (optionText.includes('원)')) {
                    const optionFull = optionText.split(' (');
                    optionName = optionFull[0].trim();
                    optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                    optionPrice = parseInt(optionPrice, 10);
                } else {
                    optionName = optionText.trim();
                    optionPrice = 0;
                }
                productOptions.push({ optionName, optionPrice });
            }
        }
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
