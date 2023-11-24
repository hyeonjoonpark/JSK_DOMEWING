const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        const username = 'luminous2020';
        const password = 'Fnalshtm88!@';
        await page.goto('https://dometopia.com/member/login');
        await page.waitForSelector('#userid');
        await page.waitForSelector('#password');
        await page.waitForSelector('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
        await page.type('#userid', username);
        await page.type('#password', password);
        await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
        await page.waitForNavigation();
        await page.goto(href);
        await page.waitForSelector('#info > div.goods_info.clearbox > form > div.container > table > tbody > tr:nth-child(2) > td > ul:nth-child(2) > li:nth-child(3) > span');
        const productPrice = await page.$eval('#info > div.goods_info.clearbox > form > div.container > table > tbody > tr:nth-child(2) > td > ul:nth-child(2) > li:nth-child(3) > span', container => {
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