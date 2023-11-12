const puppeteer = require('puppeteer');
const mysql = require('mysql2/promise');
const path = require('path');

(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [username, password, formedExcel] = args;

        await page.goto('https://domesin.com/scm/login.html');
        await page.type('input[name="m_id"]', username);
        await page.type('input[name="m_pw"]', password);
        await page.click('input[type="image"]');
        await page.waitForTimeout(5000); // 예: 5초 동안 대기
        await page.goto('http://domesin.com/scm/M_item/item_exel_insert.html');
        await page.waitForSelector('#main > form > table > tbody > tr:nth-child(2) > td.cttd > input');
        const filePath = path.join(__dirname, '..', '..', 'assets', 'excel', 'formed', formedExcel);
        console.log(filePath);
        const elementHandle = await page.$("#main > form > table > tbody > tr:nth-child(2) > td.cttd > input");
        await elementHandle.uploadFile(filePath);
        await page.waitForSelector('#main > form > table > tbody > tr:nth-child(2) > td:nth-child(3) > input');
        await page.click('#main > form > table > tbody > tr:nth-child(2) > td:nth-child(3) > input');
        await page.waitForSelector('body > table > tbody > tr > td:nth-child(2) > table.tb01 > tbody > tr:nth-child(2) > td:nth-child(5)');
        // 엘리먼트의 텍스트를 추출합니다.
        const extractedText = await page.evaluate(() => {
            const element = document.querySelector('body > table > tbody > tr > td:nth-child(2) > table.tb01 > tbody > tr:nth-child(2) > td:nth-child(5)');
            return element.textContent;
        });
        let status = -1;
        if (extractedText === '성공') {
            status = 1;
        }
        console.log(JSON.stringify(status));
    } catch (error) {
        console.error(error);
    } finally {
        // await browser.close();
    }
})();
