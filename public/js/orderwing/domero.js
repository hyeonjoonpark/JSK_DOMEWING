const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\domero'); // Update your download path

    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://www.domero.net/index.php?PAGECODE=member/login_form&loginmode=v', { waitUntil: 'networkidle0', timeout: 0 });
        await page.type('input[name="m_id"]', username);
        await page.type('input[name="m_pw"]', password);
        await page.click('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(5) > td > input');
        await page.waitForNavigation();
        await page.goto('http://www.domero.net/vms/shop_order/list.php?o_status=1', { waitUntil: 'domcontentloaded', timeout: 0 });
        await page.click('#content > table.tb13 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]');
        await new Promise((page) => setTimeout(page, 3000));
        page.on('dialog', async dialog => {
            await dialog.accept();
            console.log(true);
            return;
        });
        await page.click('#content > div > input.bt_red');
        await new Promise((page) => setTimeout(page, 3000));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
