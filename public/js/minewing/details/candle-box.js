const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        // const [tempFilePath, username, password] = args;
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const username = 'jskorea2022';
        const password = 'Tjddlf88!@#';
        const urls = ['https://candle-box.com/product/%ED%94%84%EB%9E%98%EA%B7%B8%EB%9F%B0%EC%8A%A4%EC%98%A4%EC%9D%BC-%EB%94%A5%EB%94%94-%ED%83%90%EB%8B%A4%EC%98%A4-%EB%B8%8C%EB%9E%9C%EB%93%9C-%ED%83%80%EC%9E%85/4556/category/42/display/1/'];
        await signIn(page, username, password);

        const products = [];
        for (const url of urls) {
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const productOptions = await checkedOption(page, url);
            const product = await scrapeProduct(page, url, productOptions);
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


async function checkedOption(page) {
    const hasOption = await page.evaluate(() => {
        const optionElement = document.querySelector('select.ProductOption0');
        let hasOption = false;
        if (optionElement) {
            hasOption = true;
        }
        return hasOption;
    });
    let options = [];
    if (hasOption === true) {
        options = await scrapeProductOptions(page);
    }
    const optionSet = { hasOption, options };
    return optionSet;
}


async function scrapeProductOptions(page) {
    const allSelectElements = await page.$$('select.ProductOption0');

    let productOptions = [];
    if (allSelectElements.length > 0) {// 옵션이 있다.
        if (allSelectElements.length == 1) {//옵션이 1개
            productOptions = await page.evaluate(() => {
                const optionElements = document.querySelectorAll('#product_option_id1 option');
                const productOptions = [];
                for (let i = 2; i < optionElements.length; i++) {
                    const optionElement = optionElements[i];
                    const optionText = optionElement.textContent.trim();
                    let optionName, optionPrice;

                    if (optionText.includes('원')) {
                        const optionFull = optionText.split(' (');
                        optionName = optionFull[0].trim();
                        optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                        optionPrice = parseInt(optionPrice, 10);
                    } else {
                        optionName = optionText.trim();
                        optionPrice = 0;
                    }
                    productOptions.push({ optionName, optionPrice });
                }
                return productOptions;
            });
        }

        if (allSelectElements.length == 2) {//옵션이 2개
            const allSelectElements = await page.$$('select');
            if (allSelectElements.length > 0) {
                const firstOptionValues = await page.evaluate(() => {
                    const firstOptionElements = document.querySelectorAll('#product_option_id1 option');
                    const firstOptionValues = [];
                    for (let i = 2; i < firstOptionElements.length; i++) {
                        const optionValue = firstOptionElements[i].value;
                        firstOptionValues.push(optionValue);
                    }
                    return firstOptionValues;
                });

                let productOptions = [];
                for (let i = 0; i < firstOptionValues.length; i++) {
                    const optionValue = firstOptionValues[i];
                    await page.select('select#product_option_id1', optionValue);
                    const tmpProductOptions = await page.evaluate(() => {
                        const firstOptionName = document.querySelector('#product_option_id1').selectedOptions[0].textContent.trim();
                        const secondOptionElements = document.querySelectorAll('#product_option_id2 option');
                        const options = Array.from(secondOptionElements).slice(2).map(option => {
                            const text = option.textContent.trim();
                            let price = 0;
                            if (text.includes('원')) {
                                const [name, priceText] = text.split(' (');
                                price = parseInt(priceText.replace(/[^\d-+]/g, ''), 10);
                                return { optionName: firstOptionName + ' ' + name.trim(), optionPrice: price };
                            }
                            return { optionName: firstOptionName + ' ' + text, optionPrice: price };
                        });
                        return options;
                    });
                    productOptions.push(tmpProductOptions);
                }
                return productOptions;
            }
            return [];
        }
    }
    return productOptions;
}
async function scrapeProduct(page, productHref, options) {//여기에 옵션을 넣어주고 있으면 넣어주고 없으면 안넣으면 되겠네?
    const product = await page.evaluate((productHref, options) => {

        const rawName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h2').textContent;
        const productName = removeSoldOutMessage(rawName);
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');

        const productImage = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > div > a > img').src;
        const baseUrl = window.location.origin;
        const toAbsoluteUrl = (src, baseUrl) => {
            if (src.startsWith('http://') || src.startsWith('https://')) {
                return src;
            } else {
                return new URL(src, baseUrl).href;
            }
        };
        const images = document.querySelectorAll('#prdDetail > div.cont > p img');
        if (images.length < 1) {
            return false;
        }

        const productDetail = Array.from(images, img => toAbsoluteUrl(img.src, baseUrl));


        const hasOption = options.hasOption;
        const productOptions = options.options;
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
    }, productHref, options);
    return product;
}
