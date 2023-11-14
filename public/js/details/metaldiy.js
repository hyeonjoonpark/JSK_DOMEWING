const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        await page.goto(href);
        await page.waitForSelector('#detail > img');
        const imgSrc = await page.$eval('#detail > img', img => img.src);
        const productDetail = '<center><img src="' + imgSrc + '"></center>';
        const vendor = "철물박사";
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