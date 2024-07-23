const fs = require('fs');
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product === false) {
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

async function signIn(page, username, password) {
    await page.goto('https://www.zentrade.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    await page.type('input[name="m_id"]', username);
    await page.type('input[name="password"]', password);
    await page.click('#mainloginform > table > tbody > tr > td > input[type=image]:nth-child(3)');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;
        const productData = await page.evaluate(() => {
            const productName = document.querySelector('body > table > tbody > tr:nth-child(2) > td > table > tbody > tr > td.outline_side > div.indiv > div:nth-child(2) > table > tbody > tr > td:nth-child(2) > font > b').textContent.trim();
            const productPrice = document.querySelector('#price').textContent.trim().replace(/[^\d]/g, '');
            const productImages = document.querySelectorAll('body > table > tbody > tr:nth-child(2) > td > table > tbody > tr > td.outline_side > div.indiv > div:nth-child(4) > div:nth-child(1) > div:nth-child(3) > table > tbody > tr > td img');
            const productImage = productImages[productImages.length - 1].src;
            const productDetailElements = document.querySelectorAll('#contents img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if (tempProductDetailSrc === 'http://zentrade.hgodo.com/productimgs/zt/notice.jpg' || tempProductDetailSrc === 'http://buzz71.godohosting.com/start/common/open_notice.jpg') {
                    continue;
                }
                productDetail.push(tempProductDetailSrc);
            }
            const productData = {
                productName,
                productPrice,
                productImage,
                productDetail
            };
            return productData;
        });
        const { productName, productPrice, productImage, productDetail } = productData;
        const product = {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 92
        };
        return product;
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '== 옵션선택 ==')
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
                    let optionPrice = 0;
                    newSelectedOptions.forEach(opt => {
                        let optText = opt.text;
                        const optionPriceMatch = optText.match(/\(\+?([\d,]+)원\)/);
                        if (optionPriceMatch) {
                            const optPrice = parseInt(optionPriceMatch[1].replace(/,/g, ''));
                            optionPrice += optPrice;
                            optText = optText.replace(optionPriceMatch[0], '').trim();
                        }
                        optionName = optionName.length > 0 ? `${optionName} / ${optText}` : optText;
                    });
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
        hasOption: true,
        productOptions: productOptions
    };
}
