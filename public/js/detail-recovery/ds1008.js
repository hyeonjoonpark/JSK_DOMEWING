const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    const products = [];
    try {
        const args = process.argv.slice(2);
        const [filePath, username, password] = args;
        const products = JSON.parse(fs.readFileSync(filePath, 'utf8'));
        await signIn(page, username, password);
        const fetchedProducts = [];
        for (const product of products) {
            const fetchedProduct = await buildProduct(page, product);
            if (fetchedProduct !== false) {
                fetchedProducts.push(fetchedProduct);
            }
        }
        console.log(JSON.stringify(fetchedProducts));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
    return products;
})();
async function buildProduct(page, product) {
    try {
        await page.goto(product.productHref, { waitUntil: 'domcontentloaded' });
        const productDetailImages = await page.evaluate(() => {
            const productDetailImageElements = document.querySelectorAll('div.txt-manual img');
            const productDetailImages = [];
            for (const element of productDetailImageElements) {
                const productDetailImage = element.getAttribute('src').trim();
                productDetailImages.push(productDetailImage);
            }
            return productDetailImages;
        });
        return {
            id: product.id,
            productDetailImages
        };
    } catch (error) {
        return false;
    }
}
async function signIn(page, username, password) {
    await page.goto('https://www.ds1008.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation();
}
