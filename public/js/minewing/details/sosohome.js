const fs = require('fs');
const puppeteer = require('puppeteer');
const { goToAttempts, scrollDown, signIn, checkImageUrl, checkProductName, formatProductName } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ 'width': 1500, 'height': 1000 });
    await page.setDefaultNavigationTimeout(0);

    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
    try {
        await signIn(page, username, password, 'https://sosohome.co.kr/member/login.html', '#member_id', '#member_passwd', 'div > div > fieldset > a.loginBtn');
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
        sellerID: 83
    };
}
async function isValid(page) {
    return await page.evaluate(() => {
        const isSoldOut = Array.from(document.querySelectorAll('div.infoArea > span.icon > img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
        if (isSoldOut) {
            return false;
        }
        return true;
    });
}
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('div.infoArea > h2');
        if (!productNameElement) {
            return false;
        }
        return productNameElement.textContent.trim();
    });

    if (!productName) {
        return false;
    }

    const validProductName = await checkProductName(productName);
    if (!validProductName) {
        return false;
    }

    return await formatProductName(productName);
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
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('div.keyImg.item > div.thumbnail > a > img');
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
        const productDetailElements = document.querySelectorAll('#prdDetail > div.cont > p > img');
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
        const productOptionElements = document.querySelectorAll('#product_option_id1 > optgroup option');
        const productOptions = Array.from(productOptionElements)
            .map(poe => {
                const text = poe.textContent.trim();
                const match = text.match(/(.+?)\s*\(\+\s*([0-9,]+)원\)$/);
                let optionName = text;
                let optionPrice = 0;

                if (match) {
                    optionName = match[1];
                    optionPrice = parseInt(match[2].replace(/,/g, ''));
                }

                return {
                    optionName,
                    optionPrice
                };
            })
            .filter(option => !option.optionName.includes('품절'));

        if (productOptionElements.length > 0 && productOptions.length < 1) {
            return false;
        }
        return productOptions;
    });
}
