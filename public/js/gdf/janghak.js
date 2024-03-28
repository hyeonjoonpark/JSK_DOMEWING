const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath] = args;
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const invalidProducts = [];
        for (const product of products) {
            const { productCode, productHref } = product;
            const isValid = await validateProduct(page, productHref);
            if (isValid === false) {
                invalidProducts.push(productCode);
            }
        }
        console.log(JSON.stringify(invalidProducts));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function validateProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'load' });
        const isValid = await page.evaluate(() => {
            const tagElement = document.querySelector('span.bg-noretn');
            if (tagElement) {
                const tagText = tagElement.textContent.trim();
                if (tagText.includes('온라인 판매금지')) {
                    return false;
                }
            }
            return true;
        });
        return isValid;
    } catch (error) {
        return false;
    }
}
