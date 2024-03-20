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
            const product = await scrapeProduct(page, url);
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
    await page.goto('https://dome4u.co.kr/home/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#id', username);
    await page.type('#passwd', password);
    await page.click('#login_submit');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await page.evaluate(() => {
            const priceSelector = '#buy_info > div > div.detailView.type2 > dl > dd.priceView > ul > li:nth-child(1) > strong';
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
                const detailSelector = '#content .detailInfo.detailInner img';
                const productDetailElements = document.querySelectorAll(detailSelector);
                const productDetail = [];
                for (const productDetailElement of productDetailElements) {
                    productDetail.push(productDetailElement.src);
                }
                if (productDetail.length < 1) {
                    return false;
                }
                const productNameSelector = '#buy_info > div > div.detailView.type2 > dl > dt > span';
                const productNameElements = document.querySelectorAll(productNameSelector);
                const productNameElement = Array.from(productNameElements).find(el => !el.classList.contains('ownershop'));
                const productName = productNameElement ? productNameElement.textContent.trim() : '';

                const baseUrl = window.location.origin;
                const imageSelector = '#viewImage_m';
                const imageElement = document.querySelector(imageSelector);
                const srcxx = imageElement.getAttribute('srcxx');
                const productImage = new URL(srcxx, baseUrl).href;
                return {
                    productName,
                    productPrice,
                    productImage,
                    productDetail,
                    hasOption,
                    productOptions,
                    productHref,
                    sellerID: 32
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
