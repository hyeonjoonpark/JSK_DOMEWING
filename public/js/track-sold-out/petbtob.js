const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const soldOutProducts = [];
        for (const product of products) {
            const ivp = await isValidProduct(page, product.productHref);
            if (ivp === false) {
                const soldOutProduct = {
                    id: product.id,
                    productHref: product.href
                };
                soldOutProducts.push(soldOutProduct);
            }
        }
        console.log(JSON.stringify(soldOutProducts));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.evaluate((username, password) => {
        document.querySelector('#member_id').value = username;
        document.querySelector('#member_passwd').value = password;
        document.querySelector('#contents > form > div > div > fieldset > a').click();
    }, username, password);
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function isValidProduct(page, productHref) {
    try {
        await page.goto(productHref, { waitUntil: 'domcontentloaded' });
        return await page.evaluate(() => {
            // 품절인 상품이면 false를 return 할 것.
            return true;
        });
    } catch (error) {
        return false;
    }
}
