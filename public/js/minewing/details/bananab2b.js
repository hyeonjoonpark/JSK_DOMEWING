const fs = require('fs');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product !== false) {
                products.push(product);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://bananab2b.shop/login?redirect=/', { waitUntil: 'networkidle0' });
    await page.type('input[class="TextField_input__hOlLE"]', username);
    await page.type('input[type="password"]', password);
    await page.click('#__next > div.PageLayout_layout-container__dJ80A.PageLayout_topBanner__r3_gf.PageLayout_top__9MJc2.PageLayout_header__vbEvv.PageLayout_gnb__Yvsel > div.PageLayout_content__WCS1_ > div > section > div > form > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productOptionData = await getProductOptions(page);
        const productData = await extractProductData(page);
        if (!productData) return false;
        const { productName, productPrice, productImage, productDetail } = productData;
        return {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption: productOptionData.hasOption,
            productOptions: productOptionData.productOptions,
            productHref: url,
            sellerID: 56
        };
    } catch (error) {
        console.error(error);
        return false;
    }
}
async function extractProductData(page) {
    const filterUrls = [
        'bmwa007.godohosting.com/detail_nt.jpg'
    ];
    return page.evaluate((filterUrls) => {
        const skipProductsEl = document.querySelectorAll('.ProductDetail_tag-list__UBBPk span');
        for (const element of skipProductsEl) {
            if (element.textContent.trim() === '폐쇄몰') {
                return false;
            }
        }
        const productName = document.querySelector('.ProductDetail_title-button__ZBnqo > .ProductDetail_title__JikYt')?.textContent.trim();
        const productPrice = document.querySelector('.Price_price-wrapper__jTdRi')?.textContent.trim().replace(/[^\d]/g, '');
        const productImageEl = document.querySelector('.ProductDetail_thumbnail__KX26C > img')?.src;
        if (!productImageEl) return false;
        const regex = /url=([^&]+)/;
        const match = productImageEl.match(regex);
        const productImage = match ? decodeURIComponent(match[1]) : productImageEl;
        const productDetailElements = document.querySelectorAll('.ProductDetail_gap__KbFAP > .ProductDetail_content__wenPZ img');
        if (productDetailElements.length < 1) return false;
        const productDetail = Array.from(productDetailElements)
            .map(el => el.src)
            .filter(src => !filterUrls.some(filterUrl => src.includes(filterUrl)));
        if (productDetail.length < 1) return false;
        return { productName, productPrice, productImage, productDetail };
    }, filterUrls);
}
async function getProductOptions(page) {
    const optionElements = await reloadSelects(page);
    if (optionElements.length < 1) {
        return { hasOption: false, productOptions: [] };
    }
    const productOptions = [];
    if (optionElements.length === 1) {
        await selectOption(page, optionElements[0]);
        await processSingleOption(page, productOptions);
    } else {
        await processMultipleOptions(page, productOptions, optionElements);
    }
    return { hasOption: true, productOptions };
}
async function reloadSelects(page) {
    return page.$$('div.ProductDetail_detail-wrapper__bj3TT > div.ProductDetail_option-wrapper__R8tux > table.option-table > tbody > tr > td > div.ProductDetail_option-component-wrapper__ezhW4 > div.ProductDetail_option-dropdown-wrapper__HlQLO > div.Dropdown_dropdown-wrapper__YX4fj.ProductDetail_option-dropdown__MEg1Q.Dropdown_L__aCX5e');
}
async function selectOption(page, option) {
    await option.click();
    await page.waitForSelector('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div > span');
}
async function extractOptions(page) {
    return page.evaluate(() => {
        const optionElements = document.querySelectorAll('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div');
        return Array.from(optionElements).map(option => {
            const optionText = option.querySelector('span:nth-of-type(1)')?.textContent.trim();
            const isSoldOut = option.querySelector('span:nth-of-type(2)')?.textContent.includes('품절') || false;
            return { optionText, isSoldOut };
        }).filter(({ isSoldOut }) => !isSoldOut);
    });
}
async function processSingleOption(page, productOptions) {
    const options = await extractOptions(page);
    for (const { optionText } of options) {
        const optionPriceMatch = optionText.match(/[\+\-]([\d,]+)원/);
        const optionPrice = optionPriceMatch ? parseInt(optionPriceMatch[1].replace(/,/g, ''), 10) : 0;
        const cleanedOptionText = optionText.replace(/\([\+\-]([\d,]+)원\)/, '').replace(/\(.*?\)/, '').trim();
        productOptions.push({ optionName: cleanedOptionText, optionPrice });
    }
}
async function processMultipleOptions(page, productOptions, optionElements) {
    for (let i = 0; i < optionElements.length - 1; i++) {
        await selectOption(page, optionElements[i]);
        const firstOptions = await extractOptions(page);
        for (let j = 0; j < firstOptions.length; j++) {
            const { optionText: firstOptionText, isSoldOut: firstIsSoldOut } = firstOptions[j];
            if (firstIsSoldOut) continue;
            await selectOption(page, optionElements[i]);
            await page.evaluate(index => document.querySelectorAll('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div')[index].click(), j);
            await selectOption(page, optionElements[i + 1]);
            const secondOptions = await extractOptions(page);
            for (const { optionText: secondOptionText, isSoldOut: secondIsSoldOut } of secondOptions) {
                if (secondIsSoldOut) continue;
                let optionName = `${firstOptionText} ${secondOptionText}`;
                const optionPriceMatch = optionName.match(/[\+\-]([\d,]+)원/);
                const optionPrice = optionPriceMatch ? parseInt(optionPriceMatch[1].replace(/,/g, ''), 10) : 0;
                optionName = optionName.replace(/\([\+\-]([\d,]+)원\)/, '').replace(/\(.*?\)/, '').trim();
                productOptions.push({ optionName, optionPrice });
            }
            await page.evaluate(() => document.querySelector('body').click());
        }
    }
}
