const fs = require('fs');
const puppeteer = require('puppeteer');
const { goToAttempts, checkImageUrl, checkProductName, formatProductName } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setDefaultNavigationTimeout(0);
    const [tempFilePath] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
    // const urls = ['https://smartstore.naver.com/yphm/products/3308823132'];

    try {
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
    const productName = await getProductName(page);
    if (!productName) {
        return false;
    }
    const productPrice = await getProductPrice(page);
    if (!productPrice) {
        return false;
    }
    const productImage = await getProductImage(page);
    if (!productImage) {
        return false;
    }
    const productDetail = await getProductDetail(page);
    if (!productDetail) {
        return false;
    }
    const { hasOption, productOptions } = await getProductOptions(page);
    return {
        productName,
        productPrice,
        productImage,
        productDetail,
        hasOption,
        productOptions,
        productHref,
        sellerID: 84
    };
}

async function isValid(page) {
    return await page.evaluate(() => {
        const isSoldOut = Array.from(document.querySelectorAll('span.icon > img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/common/icon_sellout.gif');
        return !isSoldOut;
    });
}

async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('#content > div > div > div > fieldset > div > div > h3');
        if (!productNameElement) {
            return null;
        }
        return productNameElement.textContent.trim();
    });

    if (!productName) {
        return null;
    }

    const validProductName = await checkProductName(productName);
    if (!validProductName) {
        return null;
    }

    return await formatProductName(productName);
}

async function getProductPrice(page) {
    return await page.evaluate(() => {
        const productPriceElement = document.querySelector('#content > div > div > div > fieldset > div > div > div > strong > span._1LY7DqCnwR');
        if (!productPriceElement) {
            return null;
        }
        return productPriceElement.textContent.replace(/[^0-9]/g, '').trim();
    });
}

async function getProductImage(page) {
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('#content > div > div > div > div > div > img');
        return productImageElement ? productImageElement.src : null;
    });

    if (!imageUrl) {
        return null;
    }

    const isValid = await checkImageUrl(imageUrl);
    return isValid ? imageUrl : null;
}

async function getProductDetail(page) {
    const imageUrls = await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('div > div > div.se-module.se-module-image > a img');
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

    return validImageUrls.length > 0 ? validImageUrls : [];
}

async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('table select');
    }

    async function reselectOptions(selects, selectedOptions) {
        for (let i = 0; i < selectedOptions.length; i++) {
            await selects[i].select(selectedOptions[i].value);
            await new Promise(resolve => setTimeout(resolve, 1000));
            if (i < selectedOptions.length - 1) {
                selects = await reloadSelects();
            }
        }
    }

    async function processSelectOptions(selects, currentDepth = 0, selectedOptions = [], productOptions = []) {
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '-1')
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));

                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];
                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = "";
                    newSelectedOptions.forEach(opt => {
                        let optText = opt.text;
                        optionName = optionName.length > 0 ? `${optionName} / ${optText}` : optText;
                    });

                    const optionPriceMatch = optionName.match(/\(([\d,]+)원\)/);
                    const optionPrice = parseInt(optionPriceMatch ? optionPriceMatch[1].replace(/,/g, '') : "0");
                    optionName = optionName.replace(/\(([\d,]+)원\)/, '').trim();

                    const productOption = { optionName, optionPrice };
                    productOptions.push(productOption);
                }

                selects = await reloadSelects();
                if (currentDepth > 0) {
                    await reselectOptions(selects, selectedOptions);
                    selects = await reloadSelects();
                }
            }
        }
        return productOptions;
    }

    let selects = await reloadSelects();
    if (selects.length < 1) {
        return {
            hasOption: false,
            productOptions: []
        };
    }

    const productOptions = await processSelectOptions(selects);
    return {
        hasOption: productOptions.length > 0,
        productOptions
    };
}
