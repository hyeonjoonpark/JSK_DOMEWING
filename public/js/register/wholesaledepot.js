const puppeteer = require('puppeteer');
const mysql = require('mysql2/promise');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, formedExcel] = args;
        const filePath = path.join(__dirname, '..', '..', 'assets', 'excel', 'formed', formedExcel);
        await page.goto('https://www.wholesaledepot.co.kr/wms/member/login.php');
        const inputID = await page.waitForSelector('#id');
        const inputPW = await page.waitForSelector('#passwd');
        const buttonSubmit = await page.waitForSelector('button[type="submit"]');
        await inputID.type('sungiltradekorea');
        await inputPW.type('tjddlf88!@');
        await buttonSubmit.click();
        await page.waitForNavigation();
        const productRegister = await page.waitForSelector('a[data="/wms/goods/goods_excel.php"]');
        await productRegister.click();
        await page.goto('https://www.wholesaledepot.co.kr/wms/goods/goods_excel.php');
        const inputFile = await page.waitForSelector('#excel');
        await page.waitForSelector('#modal_close');
        await page.waitForSelector('#btn_indb');
        await inputFile.uploadFile(filePath);
        page.on('dialog', async dialog => {
            await dialog.accept();
        })
        await page.click('#modal_close');
        const sbmBtn = await page.waitForSelector('#btn_indb');
        await sbmBtn.click();
        await sbmBtn.click();
        await page.waitForNavigation();
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
