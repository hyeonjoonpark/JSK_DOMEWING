const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        await page.goto(href);
        await page.waitForSelector('#detail > div > div.section.info > div.goods_description > div.detail-img > img');
        const imgSrc = await page.$eval('#detail > div > div.section.info > div.goods_description > div.detail-img > img', img => img.src);
        const productDetail = '<center><img src="' + imgSrc + '"></center>';
        const vendor = await page.$eval(
            '#detail > div > div.section.info > div.goods_description > div.product-info-alert > table > tbody > tr:nth-child(2) > td',
            element => element.textContent.trim()
        );
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