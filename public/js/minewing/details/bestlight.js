const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
    await page.goto('https://bestlight.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a > img');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await page.evaluate(async () => {
        const distance = 85;
        const scrollInterval = 15;
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.getElementById('prdDetail');
            const prdInfoElement = document.getElementById('prdInfo');
            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                break;
            } else {
                window.scrollBy(0, distance);
            }

            await new Promise(resolve => setTimeout(resolve, scrollInterval));
        }
    });

    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const productName = document.querySelector('#sunny_contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.xans-element-.xans-product.xans-product-detaildesign > table > tbody > tr:nth-child(1) > th > span').textContent.trim();
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
        const productImageElement = document.querySelector('#sunny_contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div > div > img').getAttribute('src').trim();
        const productImage = `https:${productImageElement}`;
        const images = document.querySelectorAll('#prdDetail > div img');
        const productDetailImageElement = [];
        const baseUrl = 'https://bestlight.co.kr/';
        images.forEach((image) => {
            const imageUrl = image.getAttribute('src').trim();
            const fullUrl = new URL(imageUrl, baseUrl).toString();
            productDetailImageElement.push(fullUrl);
        });
        const productDetail = productDetailImageElement;
        const optionElement = document.querySelector('#product_option_id1');
        let hasOption = false;
        let productOptions = [];
        if (optionElement) {
            hasOption = true;
            const optionElements = document.querySelectorAll('#product_option_id1 option');
            for (let i = 2; i < optionElements.length; i++) {
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
                if (skipSoldOutOption(optionName)) {
                    continue;
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
            sellerID: 44
        };
        function skipSoldOutOption(optionName) {
            if (optionName.includes('[품절]')) {
                return true;
            }
        }
    }, productHref);
    return product;

}
