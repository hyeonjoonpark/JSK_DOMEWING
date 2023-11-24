const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        const username = 'sungiltradekorea';
        const password = 'tjddlf88!@';
        await page.goto('http://babonara.co.kr/shop/member/login.php?&');
        await page.waitForSelector('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]');
        await page.waitForSelector('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]');
        await page.waitForSelector('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
        await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
        await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
        await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
        await page.waitForNavigation();
        await page.goto(href);
        await page.waitForSelector('#contents > table > tbody > tr > td');
        await page.waitForSelector('#price');
        const productPrice = await page.$eval('#price', container => {
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