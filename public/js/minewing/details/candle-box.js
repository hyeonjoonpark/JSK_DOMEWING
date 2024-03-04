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
    const optionCount = await page.$$('select.ProductOption0');

    let productOptions = [];

    if (optionCount.length > 0) {
        if (optionCount.length == 1) {
            productOptions = await page.evaluate(() => {
                const firstOption = document.querySelectorAll('#product_option_id1 option');
                const options = [];
                for (let i = 2; i < firstOption.length; i++) {
                    const optionElement = firstOption[i];
                    const optionText = optionElement.textContent.trim();
                    let optionName, optionPrice;

                    if (optionText.includes('원)')) {
                        const optionOrigin = optionText.split(' (');
                        optionName = optionOrigin[0].trim();
                        optionPrice = optionOrigin[1].replace(/[^\d-+]/g, '').trim();
                        optionPrice = parseInt(optionPrice, 10);
                    } else {
                        optionName = optionText.trim();
                        optionPrice = 0;
                    }
                    options.push({ optionName, optionPrice });
                }
                return options;
            });
        }

        if (optionCount.length == 2) {
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

                for (let i = 0; i < firstOptionValues.length; i++) {
                    const optionValue = firstOptionValues[i];
                    await page.select('select#product_option_id1', optionValue);
                    const tmpProductOptions = await page.evaluate(() => {
                        const firstOptionName = document.querySelector('#product_option_id1').selectedOptions[0].textContent.trim();
                        const secondOptionElements = document.querySelectorAll('#product_option_id2 option');
                        const options = Array.from(secondOptionElements).slice(2).map(option => {
                            const text = option.textContent.trim();
                            let price = 0;
                            if (text.includes('원)')) {
                                const [name, priceText] = text.split(' (');
                                price = parseInt(priceText.replace(/[^\d-+]/g, ''), 10);
                                return { optionName: firstOptionName + ' ' + name.trim(), optionPrice: price };
                            }
                            return { optionName: firstOptionName + ' ' + text, optionPrice: price };
                        });
                        return options;
                    });
                    productOptions = productOptions.concat(tmpProductOptions);
                }
            }
        }
    }

    return productOptions;
}

async function scrapeProduct(page, productHref, options) {
    await page.evaluate(async () => {
        const distance = 45;
        const scrollInterval = 50;
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.getElementById('prdDetail');
            const prdInfoElement = document.getElementById('prdInfo');
            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                break;
            } else {
                window.scrollBy(0, distance);
            }

            await new Promise(resolve => setTimeout(resolve, scrollInterval));
        }
    });

    await new Promise((page) => setTimeout(page, 1500));

    const product = await page.evaluate((productHref, options) => {

        const rawName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h2').textContent;
        const productName = removeSoldOutMessage(rawName);
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');

        const baseUrl = 'https://candle-box.com/';
        const toAbsoluteUrl = (relativeUrl, baseUrl) => new URL(relativeUrl, baseUrl).toString();

        const getAbsoluteImageUrls = (nodeList, baseUrl, ...excludedPaths) =>
            [...nodeList]
                .filter(img => !excludedPaths.some(path => img.src.includes(path)))
                .map(img => toAbsoluteUrl(img.src, baseUrl));

        const productDetailImageElements = document.querySelectorAll('#prdDetail img');
        const excludedPaths = ['/web/img/start', '/web/img/event'];
        const productDetail = getAbsoluteImageUrls(productDetailImageElements, baseUrl, ...excludedPaths);


        const productImageElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > div > a > img');
        const productImage = toAbsoluteUrl(productImageElement.src, baseUrl); const hasOption = options.hasOption;
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
