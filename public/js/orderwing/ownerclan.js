const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\ownerclan'); // Update your download path

    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://ownerclan.com/vender/', { waitUntil: 'networkidle0' });
        const frame = page.frames().find(f => f.name() === 'vmainframe');
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(1) > input', username);
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(2) > input', password);
        await frame.evaluate(() => {
            document.querySelector("body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(3) > input[type=submit]").click();
        });
        await frame.waitForNavigation({ waitUntil: 'load' });
        await page.goto('https://ownerclan.com/vender/order_list.php', { waitUntil: 'domcontentloaded' });
        await page.select('select[name="listnum"]', '300');
        await new Promise((page) => setTimeout(page, 3000));
        page.on('dialog', async dialog => {
            await dialog.dismiss();
            return;
        });
        await page.evaluate(() => {
            document.querySelector('input[name="allcheck"]').click();
            OrderCheckExcel('1');
        });
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        // await browser.close();
    }
})();
