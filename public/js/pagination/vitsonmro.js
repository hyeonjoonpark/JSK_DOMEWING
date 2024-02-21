const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL] = args;
        await page.goto(listURL, { waitUntil: 'networkidle2' });
        const numProducts = await page.evaluate(() => {
            const numProductsText = document.querySelector('body > div.container > div > div.content > div.top_toolbar.align_side > div.tool_left > p > strong').textContent.trim();
            const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
            return numProducts;
        });
        console.log(numProducts);
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();