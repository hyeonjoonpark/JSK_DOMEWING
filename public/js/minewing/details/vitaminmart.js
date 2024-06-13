const fs = require('fs');
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = await scrapeProducts(page, urls);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function signIn(page, username, password) {
    await page.goto('https://vitaminmart.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}

async function scrapeProducts(page, urls) {
    const products = [];
    for (const url of urls) {
        const product = await scrapeProduct(page, url);
        if (product !== false) {
            products.push(product);
        }
    }
    return products;
}

async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const { hasOption, productOptions } = await getProductOptions(page);
        const productData = await page.evaluate(() => {
            const productName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.xans-element-.xans-product.xans-product-detaildesign > table > tbody > tr:nth-child(1) > td > span')?.textContent.trim();
            const productPrice = document.querySelector('#span_product_price_text')?.textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > a > img')?.src;
            const productDetailElements = document.querySelectorAll('#prdDetail > div > img');
            if (productDetailElements.length < 1) return false;

            const productDetail = Array.from(productDetailElements)
                .map(el => el.src)
                .filter(src => !['http://buzz71.godohosting.com/start/common/open_end.jpg', 'http://buzz71.godohosting.com/start/common/open_notice.jpg'].includes(src));

            return { productName, productPrice, productImage, productDetail };
        });
        if (!productData) return false;

        const { productName, productPrice, productImage, productDetail } = productData;
        return {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 70
        };
    } catch (error) {
        console.error(error);
        return false;
    }
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
                    const optionName = newSelectedOptions.map(opt => opt.text).join(' / ').replace(/\(([\d,]+)원\)/, '').trim();
                    const optionPrice = newSelectedOptions.reduce((sum, opt) => sum + (parseInt(opt.text.match(/\(([\d,]+)원\)/)?.[1].replace(/,/g, '') || 0)), 0);
                    productOptions.push({ optionName, optionPrice });
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
        return { hasOption: false, productOptions: [] };
    }

    const productOptions = await processSelectOptions(selects);
    return { hasOption: true, productOptions };
}
