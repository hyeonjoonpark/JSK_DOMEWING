const fs = require('fs');
const puppeteer = require('puppeteer');
const { scrollDown, goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName, trimProductCodes } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    await page.setDefaultNavigationTimeout(0);
    await page.setViewport({ 'width': 1500, 'height': 1000 });
    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

    try {
        await signIn(page, username, password, 'https://bagissue.kr/member/login.html', '#member_id', '#member_passwd', 'span.login_btn');
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
        sellerID: 81
    };
}

async function getProductName(page) {

    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('div.name > span');
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

        const salePriceElement = document.querySelector('#span_product_price_text');
        if (!salePriceElement) {
            return false;
        }

        return salePriceElement.textContent.replace(/[^0-9]/g, '').trim();
    });
}
async function getproductImage(page) {
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('#add_slider div > li > img');
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
        let productDetailElements = document.querySelectorAll('#prdDetail > div img');

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
        const productOptionElements = document.querySelector('.item_buy select');

        if (!productOptionElements) {
            return [];
        }

        let filteredOptions = [];
        const productOptions = Array.from(productOptionElements.children)
            .filter(poe => {
                if (poe.tagName === 'OPTGROUP' && poe.children.length > 0) {
                    return true;
                } else if (poe.tagName === 'OPTION') {
                    const optionText = poe.textContent?.trim() || '';
                    return !optionText.includes('품절') &&
                        !optionText.includes('-------------------') &&
                        !optionText.includes('색상') &&
                        !optionText.includes('- [필수] 옵션을 선택해 주세요 -');
                }
                return false;
            })
            .forEach(poe => {
                if (poe.tagName === 'OPTGROUP' && poe.children.length > 0) {
                    Array.from(poe.children).forEach(groupOptChild => {
                        const optionText = groupOptChild.textContent?.trim() || '';
                        if (!optionText.includes('품절')) {
                            filteredOptions.push({
                                optionName: optionText,
                                optionPrice: '0'
                            });
                        }
                    });
                } else if (poe.tagName === 'OPTION') {
                    const optionText = poe.textContent?.trim() || '';
                    if (optionText) {
                        filteredOptions.push({
                            optionName: optionText,
                            optionPrice: '0'
                        });
                    }
                }
            });

        return filteredOptions;
    });
}
