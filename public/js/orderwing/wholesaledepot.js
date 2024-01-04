const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\wholesaledepot'); // Update your download path

    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://www.wholesaledepot.co.kr/wms/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('#id', username);
        await page.type('#passwd', password);
        await page.click('#frm > div > div > button');
        await page.waitForNavigation();
        await page.goto('https://www.wholesaledepot.co.kr/wms/order/order_list.php', { waitUntil: 'networkidle2' });
        await page.click('#chkIdxAll');
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        await page.click('#btn_indb');
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
