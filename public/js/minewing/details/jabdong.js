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
    await page.goto('https://jabdong.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div.login > fieldset > a img');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const checkSkipProduct = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.guideArea > p');
        let checkSkip = true;
        if (checkSkip) {
            const textContent = checkSkipProduct.textContent.trim();
            if (textContent.includes('최소주문수량 1개 이상')) checkSkip = false;

            if (checkSkip) return false;
        }
        const productName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > a > img').getAttribute('alt').trim();
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
        const productImageElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > a > img').getAttribute('src').trim();
        const productImage = `https:${productImageElement}`;
        const images = document.querySelectorAll('#prdDetail > div.cont img');
        const productDetailImageElement = [];
        images.forEach((image) => {
            const imageUrl = image.getAttribute('src').trim();
            if (!imageUrl.includes('Brand') && !imageUrl.includes('Notice')) {
                productDetailImageElement.push(`https:${imageUrl}`);
            }
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
                optionName = removeSoldOutOption(optionName);
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
            sellerID: 21
        };
        function removeSoldOutOption(optionName) {
            if (optionName.includes(' [품절]')) {
                return optionName.replace(' [품절]', '');
            }
            return optionName;
        }
    }, productHref);
    return product;

}
