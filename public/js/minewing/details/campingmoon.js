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
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://campingmoon.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('#loginarea > div > div.mlogin > fieldset > ul.logbtn > li > a > img');
    await page.waitForNavigation();
}
async function scrapeProduct(page, productHref) {
    try {
        const productImage = await getProductImage(page);
        if (productImage.includes('img_product_big.gif')) {
            return false;
        }
        const productDetail = await getProductDetail(page);
        if (productDetail === false) {
            return false;
        }
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption ? await getProductOptions(page) : [];
        const productPrice = await page.evaluate(() => {
            const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 36
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}

async function getProductDetail(page) {
    return await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#prdDetail > div img');
        if (productDetailElements.length > 0) {
            return Array.from(productDetailElements, element => element.src);
        }
        return false;
    });
}

async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > a > img').src;
    });
    return productImage;
}
async function getProductOptions(page) {
    async function reloadSelects() {
        return page.$$('select.ProductOption0');
    }

    async function resetSelects() {
        const delBtn = await page.$('#option_box1_del');
        if (delBtn) {
            await delBtn.click();
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
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
                opts.map(opt => ({ value: opt.value, text: opt.text }))
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**' && !opt.text.includes("품절"))
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];

                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([\+\-]?\d{1,3}(,\d{3})*원\)/g, "").trim()
                    ).join(", ");
                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/\(([\+\-]?\d{1,3}(,\d{3})*원)\)/);
                        return total + (matches ? parseInt(matches[1].replace(/,|원|\+/g, ''), 10) : 0);
                    }, 0);
                    productOptions.push({ optionName, optionPrice });
                }

                await resetSelects();
                selects = await reloadSelects();
                if (currentDepth > 0) {
                    await reselectOptions(selects, selectedOptions);
                    selects = await reloadSelects();
                }
            }
        }
        return productOptions;
    }

    const selects = await reloadSelects();
    return processSelectOptions(selects);
}


async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h3');
        let productNameText = productNameElement.textContent.trim();

        productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '');

        return productNameText.trim();
    });

    return productName;
}

async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.ProductOption0');
        if (selectElements.length > 0) {
            return true;
        }
        return false;
    });
}
