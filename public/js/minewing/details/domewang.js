const puppeteer = require('puppeteer');
const fs = require('fs');
const { goToAttempts, scrollDown, signIn } = require('../common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
    try {
        await signIn(page, username, password, 'https://domewang.co.kr/member/login.html', '#member_id', '#member_passwd', 'div > div > fieldset > a > img');
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
    await scrollDown(page);
    const isValidResult = await isValid(page);
    if (!isValidResult) {
        return false;
    }
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
        sellerID: 76
    };
}
async function isValid(page) {
    return await page.evaluate(() => {
        const isSoldOut = Array.from(document.querySelectorAll('div.icon img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
        if (isSoldOut) {
            return false;
        }
        return true;
    });
}
async function getProductName(page) {
    return await page.evaluate(() => {
        const productNameElement = document.querySelector('div.xans-element-.xans-product.xans-product-detaildesign > table > tbody > tr:nth-child(1) > td > span');
        if (!productNameElement) {
            return false;
        }
        return productNameElement.textContent.trim();
    });
}
async function getproductPrice(page) {
    return await page.evaluate(() => {
        const productPriceElement = document.querySelector('#span_product_price_text');
        if (!productPriceElement) {
            return false;
        }
        return productPriceElement.textContent.replace(/[^0-9]/g, '').trim();
    });
}
async function getproductImage(page) {
    return await page.evaluate(() => {
        const productImageElement = document.querySelector('div.keyImg > a > img');
        if (!productImageElement) {
            return false;
        }
        return productImageElement.src;
    });
}
async function getproductDetail(page) {
    return await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('div.cont img');
        if (!productDetailElements) {
            return false;
        }
        const productDetail = [];
        for (const productDetailElement of productDetailElements) {
            const productDetailSrc = productDetailElement.src;
            if (productDetailSrc) {
                productDetail.push(productDetailSrc);
            }
        }
        if (productDetail.length < 1) {
            return false;
        }
        return productDetail;
    });
}
async function getproductOptions(page) {
    return await page.evaluate(() => {
        const productOptionElements = document.querySelectorAll('optgroup option');
        const productOptions = Array.from(productOptionElements)
            .map(poe => poe.textContent.trim())
            .filter(option => !option.includes('품절'));
        if (productOptionElements.length > 0 && productOptions.length < 1) {
            return false;
        }
        return productOptions;
    });
}
