const fs = require('fs');
const puppeteer = require('puppeteer');
const { scrollDown, goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName, trimProductCodes } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.setDefaultNavigationTimeout(0);
    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

    try {
        await signIn(page, username, password, 'https://kayu.co.kr/member/login.html', '#member_id', '#member_passwd', 'fieldset > a');

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
    const isValidResult = await isValid(page);
    if (!isValidResult) {
        return false;
    }
    const productInfoResult = await checkProductInfo(page);
    if (!productInfoResult) {
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
        sellerID: 85
    };
}

async function isValid(page) {
    return await page.evaluate(() => {
        const isSoldOut = Array.from(document.querySelectorAll('p.icon > img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
        return !isSoldOut;
    });
}

async function checkProductInfo(page) {
    return await page.evaluate(() => {
        const infoElement = document.querySelector('div.guideArea > p.info');
        return infoElement && infoElement.textContent.includes('최소주문수량 1개 이상');
    });
}

async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('h2.item_name');
        if (!productNameElement) {
            return false;
        }
        let name = productNameElement.textContent.trim();
        name = name.replace(/\([^)]*\)/g, '');
        name = name.replace(/★위탁판매★/g, '');
        return name.trim();
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
    await scrollDown(page);
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('#big_img_box > div > img');
        return productImageElement ? productImageElement.src : null;
    });
    if (!imageUrl) {
        return false;
    }

    const isValid = await checkImageUrl(imageUrl);
    return isValid ? imageUrl : false;
}

async function getproductDetail(page) {
    await scrollDown(page);

    const imageUrls = await page.evaluate(() => {
        const excludedUrls = [
            'https://kayu.co.kr/_wg/img/bnr_item.jpg',
            'https://kayu.co.kr/_wg/img/img_info2.jpg'
        ];
        const productDetailElements = document.querySelectorAll('#prdDetail img');
        const productDetail = [];
        productDetailElements.forEach(element => {
            if (element.src && !excludedUrls.includes(element.src)) {
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
        const productOptionElements = document.querySelectorAll('#product_option_id1 option');
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
            .filter(option => !option.optionName.includes('품절') && !option.optionName.includes('필수') && !option.optionName.includes('-------------------'));

        if (productOptionElements.length > 0 && productOptions.length < 1) {
            return false;
        }

        return productOptions;
    });
}
