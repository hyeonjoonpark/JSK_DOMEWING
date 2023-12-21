const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    // page.setDefaultNavigationTimeout(0);
    try {
        const args = process.argv.slice(2);
        const [listURL] = args;
        await page.goto(listURL, { waitUntil: 'networkidle2' });
        const numProducts = await page.evaluate(() => {
            const numProducts = document.querySelector('#content > div > div > div.cg-main > div.goods-list > span > strong').textContent;
            return parseInt(numProducts);
        });
        console.log(parseInt(numProducts));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
