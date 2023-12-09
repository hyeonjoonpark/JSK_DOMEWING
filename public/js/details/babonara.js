const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        await page.goto(href);
        await page.waitForSelector('#contents > table > tbody > tr > td');
        const productDetail = await page.$eval('#contents > table > tbody > tr > td', container => container.innerHTML);
        const data = {
            productDetail: productDetail
        };
        console.log(JSON.stringify(data));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();