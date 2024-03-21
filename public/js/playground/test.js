const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
            const product = await scrapeProduct(page, url);
            if (product === false || product === null) {
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
    await page.goto('https://campingmoon.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('#loginarea > div > div.mlogin > fieldset > ul.logbtn > li > a > img');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await page.evaluate(() => {
            const priceSelector = '#span_product_price_text';
            const productPriceText = document.querySelector(priceSelector)?.textContent.trim();
            const productPrice = parseInt(productPriceText.replace(/[^\d]/g, ''), 10);
            return productPrice;
        });

        if (!productPrice) {
            console.error('Product price could not be scraped.');
            return null;
        }

        const { hasOption, productOptions } = await getHasOption(page);

        return await page.evaluate((productHref, hasOption, productOptions, productPrice) => {
            try {
                const detailSelector = '#prdDetail > div img';
                const productDetailElements = document.querySelectorAll(detailSelector);
                const productDetail = [];
                for (const productDetailElement of productDetailElements) {
                    productDetail.push(productDetailElement.src);
                }
                if (productDetail.length < 1) {
                    return false;
                }
                const productNameElement = document.querySelector('div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h3');
                let productName = '';
                if (productNameElement) {
                    // innerHTML에서 span 태그와 내용을 제거
                    productName = productNameElement.innerHTML.replace(/<span.*?>.*?<\/span>/g, '').trim();
                };

                const baseUrl = window.location.origin;
                const imageSelector = 'div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > a > img';
                const imageElement = document.querySelector(imageSelector);
                const src = imageElement ? imageElement.getAttribute('src') : '';
                const productImage = src ? new URL(src, baseUrl).href : '';

                return {
                    productName,
                    productPrice,
                    productImage,
                    productDetail,
                    hasOption,
                    productOptions,
                    productHref,
                    sellerID: 36 // 예시 값, 실제 상황에 맞게 조정해야 할 수 있습니다.
                };
            } catch (error) {
                console.error('Error in product details extraction:', error);
                return false;
            }
        }, productHref, hasOption, productOptions, productPrice);
    } catch (error) {
        console.error('Error occurred while scraping product:', error);
        return null;
    }
}

async function getHasOption(page) {
    const optionSelectElements = await page.$$('select.opt_ind_1');
    const hasOption = optionSelectElements.length > 0;
    let productOptions = [];
    if (hasOption) {
        productOptions = await generateOptions(optionSelectElements);
    }
    return {
        hasOption,
        productOptions
    };
}
async function generateOptions(selectElements) {
    let options = [];
    for (const select of selectElements) {
        const values = await select.$$eval('option', opts => opts.map(opt => opt.textContent.trim()));
        const validValues = values.slice(1);
        if (options.length === 0) {
            options = validValues.map(value => [value]);
        } else {
            let newOptions = [];
            for (const option of options) {
                for (const value of validValues) {
                    newOptions.push([...option, value]);
                }
            }
            options = newOptions;
        }
    }
    return options.map(optionCombo => ({
        optionName: optionCombo.join(", "),
        optionPrice: 0
    }));
}

