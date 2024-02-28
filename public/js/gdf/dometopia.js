const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const startUrl = 'https://dometopia.com/goods/search?search_text=GDF&page=';
        const productHrefs = [];
        for (let i = 54; i > 0; i--) {
            const fullUrl = startUrl + i;
            await page.goto(fullUrl, 'domcontentloaded');
            productHrefs.push(...await getProductHrefs(page));
        }
        console.log(JSON.stringify(productHrefs));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        // await browser.close();
    }
})();
async function getProductHrefs(page) {
    const productHrefs = await page.evaluate(() => {
        const productElements = document.querySelectorAll('dl.goodsDisplayItemWrap');
        const productHrefs = [];
        for (const productElement of productElements) {
            const productHref = productElement.querySelector('dl > dd.goodsDisplayTitle > div > a').href;
            productHrefs.push(productHref);
        }
        return productHrefs;
    });
    return productHrefs;
}