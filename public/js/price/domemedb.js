const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        const username = 'sungil2018';
        const password = 'tjddlf88!@';
        await page.goto('https://domeggook.com/ssl/member/mem_loginForm.php?back=L21haW4vaW5kZXgucGhw');
        await page.waitForSelector('#idInput');
        await page.waitForSelector('#pwInput');
        await page.waitForSelector('#formLogin > input.formSubmit');
        await page.type('#idInput', username);
        await page.type('#pwInput', password);
        await page.click('#formLogin > input.formSubmit');
        await page.goto(href);
        await page.waitForSelector('#lInfoBody > div.lInfoBody.lInfoRow.lSelected > table > tbody > tr.lInfoAmt > td > div > div');
        const productPrice = await page.$eval('#lInfoBody > div.lInfoBody.lInfoRow.lSelected > table > tbody > tr.lInfoAmt > td > div > div', container => {
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