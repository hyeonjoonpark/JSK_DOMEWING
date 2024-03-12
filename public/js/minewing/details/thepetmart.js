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
            const productOptions = await checkedOption(page);
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
    await page.goto('https://thepetmart.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > div.login_btn');
    await page.waitForNavigation();
}
async function checkedOption(page) {
    await new Promise(resolve => setTimeout(resolve, 1000));
    const hasOption = await page.evaluate(() => {
        const optionElement = document.querySelector('#product_option_id1');
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


    if (optionCount.length == 1) {
        productOptions = await page.evaluate(() => {
            const firstOption = document.querySelectorAll('#product_option_id1 option');
            const options = [];
            for (let i = 2; i < firstOption.length; i++) {
                const optionElement = firstOption[i];
                const optionText = optionElement.textContent.trim();
                let optionName, optionPrice;
                if (optionText.includes('품절')) continue;
                if (optionText.includes('￦')) {
                    const optionOrigin = optionText.split('(+');
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

                    const options = Array.from(secondOptionElements)
                        .slice(2)
                        .filter(option => !option.textContent.trim().includes('품절'))
                        .map(option => {
                            const text = option.textContent.trim();
                            let price = 0;
                            if (text.includes('￦')) {
                                const [name, priceText] = text.split('(+');
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
    return productOptions;
}
async function scrapeProduct(page, productHref, options) {
    const product = await page.evaluate((productHref, options) => {
        const productNameEl = document.querySelector('#container > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h3').textContent.trim();
        const productName = productNameEl.replace('(해외배송 가능상품)', '').trim();
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
        const productImageElement = document.querySelector('#container > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > img').getAttribute('src').trim();
        const productImage = `https:${productImageElement}`;
        const images = document.querySelectorAll('#prdDetail > div img');
        const productDetailImageElement = [];
        for (const image of images) {
            let imageUrl = image.getAttribute('src').trim();

            if (imageUrl.startsWith('/web')) {
                imageUrl = 'https://thepetmart.co.kr' + imageUrl;
            }
            else if (imageUrl.startsWith('//')) {
                imageUrl = 'https:' + imageUrl;
            }

            if (!imageUrl.includes('dingdonge.openhost') && !imageUrl.includes('openhost.dingdonge')) {
                productDetailImageElement.push(imageUrl);
            }
        }

        const productDetail = productDetailImageElement;

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
            sellerID: 28
        };
    }, productHref, options);
    return product;
}
