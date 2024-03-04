const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath] = args;
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        for (const product of products) {
            const productCode = product.productCode;
            const productHref = product.productHref;
        }
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();