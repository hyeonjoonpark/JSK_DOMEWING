const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const startUrl = 'https://dometopia.com/goods/search?search_text=GDF&page=';
        const productHrefs = [];
        for (let i = 2; i > 0; i--) {
            const fullUrl = startUrl + i;
            await page.goto(fullUrl, 'domcontentloaded');
            const tmpProductHrefs = await getProductHrefs(page);
            productHrefs.push(...tmpProductHrefs);
        }
        console.log(JSON.stringify(productHrefs));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function getProductHrefs(page) {
    return page.evaluate(() => Array.from(document.querySelectorAll('td[valign="top"] a'), element => element.href));
}
