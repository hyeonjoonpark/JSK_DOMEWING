const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath] = args;
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const productCodes = [];
        for (const product of products) {
            const productCode = product.productCode;
            const productHref = product.productHref;
            const isOverAmountResult = await isOverAmount(page, productHref);
            if (isOverAmountResult) {
                productCodes.push(productCode);
            }
        }
        console.log(JSON.stringify(productCodes));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function isOverAmount(page, productHref) {
    await page.goto(productHref, { waitUntil: 'domcontentloaded' });
    const isOverAmount = await page.evaluate(() => {
        try {
            const amountText = document.querySelector('#eachAmount').textContent.trim();
            const amount = parseInt(amountText.replace(/[^\d]/g, ''), 10);
            if (amount > 1) {
                return true;
            }
            return false;
        } catch (error) {
            return true;
        }
    });
    return isOverAmount;
}