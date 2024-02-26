const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
    await page.goto('https://candle-box.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('a[class="btnLogin"]');
    await page.waitForNavigation();
}


async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {
        const rawName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h2').textContent;
        const productName = removeSoldOutMessage(rawName);
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > div > a > img').getAttribute('src');
        const images = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-additional img');
        if (images.length < 1) {
            return false;
        }
        const productDetail = Array.from(images, img => {
            let src = img.getAttribute('src');
            return src;
        });
        const optionElement = document.querySelector('#product_option_id1');

        let hasOption = false;

        if (optionElement) {
            hasOption = true;
        }

        let productOptions = [];

        if (hasOption) {
            //const optionElements = document.querySelectorAll('tbody.xans-element-.xans-product.xans-product-option.xans-record-');
            // Array.from(optionElements).forEach(optionElement => {
            //     console.log(optionElement);
            // });
            const optionElements = document.querySelectorAll('#product_option_id1 > optgroup option');
            Array.from(optionElements).forEach(el => {
                const optionText = el.textContent.trim();
                let optionName, optionPrice;
                if (optionText.includes('원')) { // 
                    const optionFull = optionText.split(' (');
                    optionName = optionFull[0].trim();
                    optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                    optionPrice = parseInt(optionPrice, 10);
                } else {
                    optionName = optionText.trim();
                    optionPrice = 0;
                }
                productOptions.push({ optionName, optionPrice });
                // const canSplit = text.split(' ');
                // if (canSplit.length === 2) {
                //     const [optionName, optionPriceText] = text.split(' ').map(s => s.trim());
                //     let optionPrice = 0;
                //     if (optionPriceText) {
                //         optionPrice = parseInt(optionPriceText.replace(/[^\d]/g, ''), 10) + parseInt(productPrice);
                //     }
                //     productOptions.push({ optionName, optionPrice });
                // }
                // else if (canSplit.length === 1) {
                //     productOptions.push({ productName, productPrice });
                // }
            });
        }
        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 19
        };
        function removeSoldOutMessage(rawName) {
            const productName = rawName.trim();
            if (productName.includes('-품절시 단종')) {
                return productName.replace('-품절시 단종', '');
            }
            return productName;
        }
    }, productHref);
    return product;
}
