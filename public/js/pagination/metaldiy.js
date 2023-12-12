const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    // page.setDefaultNavigationTimeout(0);
    try {
        const args = process.argv.slice(2);
        const [listURL] = args;
        await page.goto(listURL);
        await page.waitForSelector('#container > div.container.wrapper_fix > div.goods_list_contents > h3 > strong');
        const numProducts = await page.evaluate(() => {
            const numProducts = document.querySelector('#container > div.container.wrapper_fix > div.goods_list_contents > h3 > strong').textContent;
            return parseInt(numProducts);
        });
        console.log(parseInt(numProducts));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
