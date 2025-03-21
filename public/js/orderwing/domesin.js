const puppeteer = require('puppeteer');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();
    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\domesin'); // Update your download path
    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://domesin.com/scm/login.html', { waitUntil: 'networkidle2' });
        await page.type('input[name="m_id"]', username);
        await page.type('input[name="m_pw"]', password);
        await page.click('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(3) > input[type=image]');
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('https://domesin.com/scm/M_order/list.html', { waitUntil: 'networkidle2' });
        await page.click('#main > table.tb13 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]');
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        await page.click('#main > div > input.bt_red');
        await new Promise((page) => setTimeout(page, 3000));
        await page.goto('http://domesin.com/scm/M_order/list.html?o_status=1', { waitUntil: 'networkidle2' });
        await page.click('#main > table.tb13 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]');
        await page.click('#main > div > input.bt_red');
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
