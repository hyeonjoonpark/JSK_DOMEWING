const fs = require('fs');
const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.setDefaultNavigationTimeout(0);
    await page.setViewport({ 'width': 1500, 'height': 1000 });
    await page.setDefaultNavigationTimeout(0);
    const [tempFilePath, username, password] = process.argv.slice(2);


    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
    try {
        await signIn(page, username, password, 'https://goodsdeco.com/member/login.php', '#loginId', '#loginPwd', 'div.login_input_sec > button');
        const products = [];
        for (const url of urls) {
            const goToAttemptsResult = await goToAttempts(page, url, 'domcontentloaded');
            if (!goToAttemptsResult) {
                continue;
            }
            const product = await buildProduct(page, url);
            if (!product) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function buildProduct(page, productHref) {

    const productName = await getProductName(page);
    if (!productName) {
        return false;
    }
    const productPrice = await getproductPrice(page);
    if (!productPrice) {
        return false;
    }
    const productImage = await getproductImage(page);
    if (!productImage) {
        return false;
    }
    const productDetail = await getproductDetail(page);
    if (!productDetail) {
        return false;
    }
    const productOptions = await getproductOptions(page);
    if (productOptions === false) return false;
    const hasOption = productOptions.length > 0;

    return {
        productName,
        productPrice,
        productImage,
        productDetail,
        hasOption,
        productOptions,
        productHref,
        sellerID: 77
    };
}

async function getProductName(page) {
    return await page.evaluate(() => {
        const productNameElement = document.querySelector('#frmView div.item_detail_tit > h3');
        if (!productNameElement) {
            return false;
        }
        return productNameElement.textContent.trim();
    });
}
async function getproductPrice(page) {
    return await page.evaluate(() => {
        const productPriceElement = document.querySelector('#frmView dl:first-child > dd > span');
        const salePriceElement = document.querySelector('#frmView dl.item_price > dd > strong');
        if (!productPriceElement && !salePriceElement) {
            return false;
        }
        const price = productPriceElement || salePriceElement;

        return price.textContent.replace(/[^0-9]/g, '').trim();
    });
}
async function getproductImage(page) {
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('#contents div.content_box li:first-child > a > img.middle');
        return productImageElement ? productImageElement.src : null;
    });

    if (!imageUrl) {
        return false;
    }

    const isValid = await checkImageUrl(imageUrl);
    return isValid ? imageUrl : false;
}
async function getproductDetail(page) {
    const imageUrls = await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#detail > div.detail_cont div.txt-manual > div > img');
        const productDetail = [];
        productDetailElements.forEach(element => {
            if (element.src) {
                productDetail.push(element.src);
            }
        });
        return productDetail;
    });

    const validImageUrls = [];
    for (const url of imageUrls) {
        const isValid = await checkImageUrl(url);
        if (isValid) {
            validImageUrls.push(url);
        }
    }

    return validImageUrls.length > 0 ? validImageUrls : false;
}


async function getproductOptions(page) {

    return await page.evaluate(() => {

        const productOptionElements = document.querySelectorAll('#frmView div.item_detail_list select option');
        const productOptions = Array.from(productOptionElements)
            .map(poe => {
                const [optionName, optionPrice] = poe.textContent.trim().split(':');
                return {
                    optionName: optionName.trim(),
                    optionPrice: optionPrice ? optionPrice.replace(/[^0-9]/g, '').trim() : '0'
                };
            })
            .filter(option => option.optionName && option.optionPrice &&
                !option.optionName.includes('옵션') && !option.optionPrice.includes('가격'));

        return productOptions.length > 0 ? productOptions : false;
    });
}

