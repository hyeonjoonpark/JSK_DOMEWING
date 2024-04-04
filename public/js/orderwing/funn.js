const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\funn'); // Update your download path

    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        await page.goto('https://scm.funn.co.kr/login/login.asp', { waitUntil: 'networkidle0' });
        await page.type('body > form > div > div:nth-child(3) > div > div:nth-child(1) > input[type=text]', username);
        await page.type('body > form > div > div:nth-child(3) > div > div:nth-child(2) > input[type=password]', password);
        await page.click('body > form > div > div:nth-child(3) > span > input[type=submit]');//로그인
        await page.waitForNavigation({ waitUntil: 'load' });
        await page.goto('https://scm.funn.co.kr/order/order_list.asp?openmenu=b01m00s01', { waitUntil: 'networkidle0' });//배송준비처리
        await page.click('#order > table > thead > tr > td:nth-child(1) > input[type=checkbox]');
        await new Promise((page) => setTimeout(page, 2000));
        await page.click('#order > table > thead > tr > td:nth-child(1) > input[type=checkbox]');
        await page.click('#control > div.right > a:nth-child(1) > span');
        await new Promise((page) => setTimeout(page, 3000));
        await page.goto('https://scm.funn.co.kr/order/order_list.asp?openmenu=b01m00s02', { waitUntil: 'networkidle0' });
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#control > div.revise > table > tbody > tr > td:nth-child(2) > a:nth-child(2) > span');

        await new Promise((page) => setTimeout(page, 3000));

        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
