const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        await page.goto('https://www.metaldiy.com/login/popupLogin.do?popupYn=Y');
        await page.waitForSelector('#loginId');
        await page.waitForSelector('#loginPw');
        await page.type('#loginId', 'sungil2018');
        await page.type('#loginPw', 'tjddlf88!@');
        await page.waitForSelector('input[title="로그인"]');
        await page.click('input[title="로그인"]');
        await page.waitForNavigation();
        await page.goto(href);
        await page.waitForSelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li.price > dl:nth-child(3) > dd > span');
        const productPrice = await page.$eval('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li.price > dl:nth-child(3) > dd > span', container => {
            // #price 요소의 내용에서 숫자가 아닌 모든 문자를 제거
            const onlyNumbers = container.innerHTML.replace(/\D/g, '');
            return onlyNumbers;
        });
        const data = {
            productPrice: productPrice
        };
        console.log(JSON.stringify(data));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();