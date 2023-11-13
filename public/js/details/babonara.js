const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        await page.goto(href);
        await page.waitForSelector('#contents > table > tbody > tr > td');
        const productDetail = await page.$eval('#contents > table > tbody > tr > td', container => container.innerHTML);
        const vendor = "상세페이지 참조";
        const data = {
            productDetail: productDetail,
            vendor: vendor
        };
        console.log(JSON.stringify(data));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();