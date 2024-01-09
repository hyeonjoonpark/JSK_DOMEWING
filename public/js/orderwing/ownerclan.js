const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
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
        await page.setViewport({
            width: 1920,
            height: 1080
        });
        await page.goto('https://ownerclan.com/vender/', { waitUntil: 'networkidle2' });
        const frame = page.frames().find(f => f.name() === 'vmainframe');
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(1) > input', username);
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(2) > input', password);
        await frame.evaluate(() => {
            document.querySelector("body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(3) > input[type=submit]").click();
        });
        await frame.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('https://ownerclan.com/vender/order_list.php', { waitUntil: 'networkidle2' });
        await page.select('body > table:nth-child(1) > tbody > tr:nth-child(6) > td > table > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(3) > table > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table:nth-child(2) > tbody > tr:nth-child(2) > td > select', '300');
        await new Promise((page) => setTimeout(page, 3000));
        await page.click('body > table:nth-child(1) > tbody > tr:nth-child(6) > td > table > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(3) > table > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table:nth-child(3) > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=checkbox]');
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        await page.click('body > table:nth-child(1) > tbody > tr:nth-child(6) > td > table > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(3) > table > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table:nth-child(1) > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td:nth-child(2) > a');
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
