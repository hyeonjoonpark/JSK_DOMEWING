const puppeteer = require('puppeteer');
const fs = require('fs');

async function main() {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [tempFilePath] = args;
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const forbiddenProductCodes = await filterForbiddenProducts(page, products);

        console.log(JSON.stringify(forbiddenProductCodes));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
}

async function filterForbiddenProducts(page, products) {
    const forbiddenProductCodes = [];

    for (let [index, product] of products.entries()) {
        if (await isForbiddenProduct(page, product.productHref, index)) {
            forbiddenProductCodes.push(product.productCode);
        }
    }

    return forbiddenProductCodes;
}

async function isForbiddenProduct(page, productHref, index) {
    try {
        const waitUntil = index === 0 ? 'networkidle0' : 'domcontentloaded';
        await page.goto(productHref, { waitUntil });

        const isOverAmount = await page.evaluate(() => {
            const amountText = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.top_title_bar > span');
            const samedayShipping = amountText.querySelector('span[style="border:1px solid transparent; background:#1d6ece; color:#FFFFFF;"]');
            if (!samedayShipping) {
                return true;
            }
            if (samedayShipping.style.display === 'none') {
                return true;
            }
            if (!amountText) return true;
            const iconTexts = amountText.textContent;
            return iconTexts.includes('인터넷판매불가');
        });

        return isOverAmount;
    } catch (error) {
        console.error(`Error in isForbiddenProduct with product ${productHref}:`, error);
        return true; // 에러가 발생했을 경우, 제품을 금지된 제품으로 간주
    }
}

main();
