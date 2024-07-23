const fs = require('fs');
const puppeteer = require('puppeteer');
const { scrollDown, goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName, trimProductCodes } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.setDefaultNavigationTimeout(0);
    await page.setViewport({ 'width': 1500, 'height': 1000 });
    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

    try {
        await signIn(page, username, password, 'https://comeonshop.co.kr/member/login.html', '#member_id', '#member_passwd', '.user-login fieldset > a');
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
    if (productOptions === false) {
        return false;
    }
    const hasOption = productOptions.length > 0;

    return {
        productName,
        productPrice,
        productImage,
        productDetail,
        hasOption,
        productOptions,
        productHref,
        sellerID: 80
    };
}

async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('div.detailArea div.buy-scroll-box > h2');
        if (!productNameElement) {
            return false;
        }
        return productNameElement.textContent.trim();
    });

    if (!productName) {
        return false;
    }
    const sanitizedProductName = productName.replace(/\(.*?\)/g, '').trim();
    const validProductName = await checkProductName(sanitizedProductName);
    if (!validProductName) {
        return false;
    }
    return await formatProductName(sanitizedProductName);
}

async function getproductPrice(page) {
    return await page.evaluate(() => {

        const salePriceElement = document.querySelector('#span_product_price_text');
        if (!salePriceElement) {
            return false;
        }

        return salePriceElement.textContent.replace(/[^0-9]/g, '').trim();
    });
}
async function getproductImage(page) {
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('#big_img_box > div > img');
        return productImageElement ? productImageElement.src : null;
    });

    if (!imageUrl) {
        return false;
    }

    const isValid = await checkImageUrl(imageUrl);
    return isValid ? imageUrl : false; j
}
async function getproductDetail(page) {
    await scrollDown(page);

    const imageUrls = await page.evaluate(() => {
        let productDetailElements = document.querySelectorAll('#prdDetail > div.cont img');

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
        const productOptionElements = document.querySelectorAll('div.detailArea select option');
        if (productOptionElements.length === 0) {
            return [];
        }
        const productOptions = Array.from(productOptionElements)
            .filter(poe => {
                const optionText = poe.textContent.trim();
                return !optionText.includes('품절') && !optionText.includes('-------------------') && !optionText.includes('- [필수] 옵션을 선택해 주세요 -');
                // Filter out sold-out optionsText  // Placeholder EMPTY optionText
            })
            .map(poe => {
                const optionText = poe.textContent.trim();
                const [optionName, optionPrice] = optionText.split(':');
                return {
                    optionName: optionName.trim(),
                    optionPrice: optionPrice ? optionPrice.replace(/[^0-9]/g, '').trim() : 0
                };
            })
            .filter(option => option.optionName) // ensure optionName and optionPrice properties

        if (productOptionElements.length > 0 && productOptions.length < 1) {
            return false;
        }
        return productOptions;
    });
}
