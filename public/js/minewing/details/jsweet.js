const fs = require('fs');
const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName } = require('../common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.setDefaultNavigationTimeout(0);
    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

    try {
        await signIn(page, username, password, 'https://www.jsweet.co.kr/member/login.html', '#member_id', '#member_passwd', 'div > fieldset > a.btnSubmit.sizeL.df-lang-button-login');
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
        sellerID: 91
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
        const productNameElement = document.querySelector('#df-product-detail > div > div.infoArea-wrap > div > div > div.scroll-wrapper.df-detail-fixed-scroll.scrollbar-macosx > div.df-detail-fixed-scroll.scrollbar-macosx.scroll-content > div.headingArea > h2');
        return productNameElement ? productNameElement.textContent.trim() : false;
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

async function getProductPrice(page) {
    return await page.evaluate(() => {
        const productPriceElement = document.querySelector('#span_product_price_text');
        return productPriceElement ? productPriceElement.textContent.replace(/[^0-9]/g, '').trim() : false;
    });
}

async function getProductImage(page) {
    const imageUrl = await page.evaluate(() => {
        const productImageElement = document.querySelector('#df-product-detail > div > div.imgArea-wrap > div > div > div.thumbnail > span > img');
        return productImageElement ? productImageElement.src : null;
    });

    if (!imageUrl) {
        return false;
    }

    const isValid = await checkImageUrl(imageUrl);
    return isValid ? imageUrl : false;
}

async function getProductDetail(page) {
    const excludedImages = [
        '/web/upload/appfiles/ZaReJam3QiELznoZeGGkMG/8836a516b11011dfaa89b7f275346c17.jpg',
        '/web/upload/appfiles/ZaReJam3QiELznoZeGGkMG/fef00cf9f94d66d92af2f03258995a87.jpg',
        '/web/upload/appfiles/ZaReJam3QiELznoZeGGkMG/b29663de1cc7231b2585990b714bbd9e.jpg',
        '/web/upload/appfiles/ZaReJam3QiELznoZeGGkMG/93e626af738f3dbba04ed4e800fac278.jpg'
    ];

    const imageUrls = await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#prdDetail img');
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
        const isExcluded = excludedImages.some(excludedUrl => url.includes(excludedUrl));
        if (!isExcluded) {
            const isValid = await checkImageUrl(url);
            if (isValid) {
                validImageUrls.push(url);
            }
        }
    }

    return validImageUrls.length > 0 ? validImageUrls : false;
}

async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('select');
    }

    async function reselectOptions(selects, selectedOptions) {
        for (let i = 0; i < selectedOptions.length; i++) {
            await selects[i].select(selectedOptions[i].value);
            await new Promise(resolve => setTimeout(resolve, 1000));  // 충분한 대기 시간을 둠
            if (i < selectedOptions.length - 1) {
                selects = await reloadSelects();
            }
        }
    }

    async function processSelectOptions(selects, currentDepth = 0, selectedOptions = [], productOptions = []) {
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not([disabled])', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '-1' && opt.value !== '*' && opt.value !== '**')
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));  // 충분한 대기 시간을 둠

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
