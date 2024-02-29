const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const username = 'jskorea2022';
        // const password = 'Tjddlf88!@#';
        // const urls = ['https://candle-box.com/product/11-%EB%84%93%EC%9D%80%EC%9E%85%EA%B5%AC%EC%8B%9C%EC%95%BD%EB%B3%91-125ml-250ml-%ED%88%AC%EB%AA%85%EA%B7%B8%EB%A6%B0%EB%B8%94%EB%A3%A8%EA%B0%88%EC%83%89-%ED%92%88%EC%A0%88%EC%8B%9C-%EB%8B%A8%EC%A2%85/1681/category/79/display/1/'];
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
    if (optionCount.length > 0) {// 옵션이 있다.
        if (optionCount.length == 1) {//옵션이 1개
            productOptions = await page.evaluate(() => {
                const firstOption = document.querySelectorAll('#product_option_id1 option');
                const productOptions = [];
                for (let i = 2; i < firstOption.length; i++) {
                    const optionElement = firstOption[i];
                    const optionText = optionElement.textContent.trim();
                    let optionName, optionPrice;

                    if (optionText.includes('원')) {
                        const optionOrigin = optionText.split(' (');
                        optionName = optionOrigin[0].trim();
                        optionPrice = optionOrigin[1].replace(/[^\d-+]/g, '').trim();
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

        if (optionCount.length == 2) {//옵션이 2개
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
async function scrapeProduct(page, productHref, options) {
    await page.waitForTimeout(1000);
    await page.evaluate(async () => {
        await new Promise((resolve, reject) => {
            const distance = 30;
            const slowScrollDistance = 10;
            const scrollInterval = 60;
            let pauseFlag = false;

            const getTargetScrollTop = (element) => {
                const elementRect = element.getBoundingClientRect();
                const offsetTop = elementRect.top + window.scrollY;
                return offsetTop - slowScrollDistance;
            };

            const timer = setInterval(() => {
                const scrollHeight = document.body.scrollHeight;
                const scrollTop = window.scrollY;

                if (pauseFlag) {
                    clearInterval(timer);
                    setTimeout(() => {
                        pauseFlag = false;
                        resolve();
                    }, 2000);
                } else {
                    window.scrollBy(0, distance);

                    const prdDetailElement = document.getElementById('prdDetail');
                    const prdInfoElement = document.getElementById('prdInfo');

                    if (prdDetailElement) {
                        const targetScrollTop = getTargetScrollTop(prdDetailElement);
                        if (scrollTop < targetScrollTop) {
                            window.scrollTo(0, targetScrollTop);
                        }
                    } else if (prdInfoElement) {
                        pauseFlag = true;
                    }

                    if (scrollTop + window.innerHeight >= scrollHeight) {
                        clearInterval(timer);
                        resolve();
                    }
                }
            }, scrollInterval);
        });
    });





    //--------------------ㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡㅡ

    const product = await page.evaluate((productHref, options) => {

        const rawName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h2').textContent;
        const productName = removeSoldOutMessage(rawName);
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');


        const baseUrl = 'https://candle-box.com/';
        // Function to convert relative URL to absolute URL
        const toAbsoluteUrl = (relativeUrl, baseUrl) => new URL(relativeUrl, baseUrl).toString();
        const getAbsoluteImageUrls = (nodeList, baseUrl) =>
            [...nodeList].map(img => toAbsoluteUrl(img.src, baseUrl));
        const productImageElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > div > a > img');
        const productImage = toAbsoluteUrl(productImageElement.src, baseUrl);
        const productDetailImageElements = document.querySelectorAll('#prdDetail img');
        const productDetail = getAbsoluteImageUrls(productDetailImageElements, baseUrl);
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
