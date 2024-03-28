const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
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
async function signIn(page, username, password) {
    await page.goto('https://housemore.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}
async function validateProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'load' });
        const isValid = await page.evaluate(() => {
            const productNameElement = document.querySelector("div.headingArea > h2");
            if (productNameElement) {
                const productNameText = productNameElement.textContent.trim();
                if (productNameText.includes('금지')) {
                    return false;
                }
                return true;
            }
            return false;
        });
        return isValid;
    } catch (error) {
        return false;
    }
}
