const puppeteer = require('puppeteer');
const mysql = require('mysql2/promise');
const path = require('path');

(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
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
        await page.waitForNavigation();
        console.log(JSON.stringify());
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();