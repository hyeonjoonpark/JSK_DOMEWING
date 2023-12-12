const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        await page.goto('https://www.metaldiy.com/login/popupLogin.do?popupYn=Y');
        await page.waitForSelector('#loginId');
        await page.waitForSelector('#loginPw');
        await page.type('#loginId', username);
        await page.type('#loginPw', password);
        await page.goto(productHref);
        await page.waitForSelector('#detail > img');
        await page.waitForSelector('#detail > img');
        await page.waitForSelector('#detail > img');
        await page.waitForSelector('#detail > img');
        const productContents = await page.evaluate(() => {
            const productName = document.querySelector('#webItemNm').value;
            const productPrice = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li.price > dl:nth-child(3) > dd > span').innerHTML;
            const productImage = document.querySelector('#zoom_goods').src;
            const productDetail = document.querySelector('#detail > img').src;
            return {
                productName: productName,
                productPrice: productPrice,
                productImage: productImage,
                productDetail: productDetail
            };
        });
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();