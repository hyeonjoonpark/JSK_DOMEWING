const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();
    await page.setViewport({
        width: 1920,
        height: 1080
    });

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\trendhunterb2b'); // Update your download path

    // Setting up Chrome to allow downloads
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
        await login(page, username, password);
        await page.goto('https://trendhunterb2b.com/ko/provider/sales-order.php', { waitUntil: 'networkidle0' });

        await page.evaluate(() => {
            document.querySelector('#check_orderState > div > label > div:nth-child(1) > label').click();
            document.querySelector('#check_orderState > div > label > div:nth-child(2) > label').click();
            document.querySelector('button[class="btn d-inline-block"]').click();
        });
        await page.waitForNavigation({ waitUntil: 'networkidle0' });
        await page.evaluate(async () => {
            document.querySelector('body > div.page-content > div.content-wrapper > div.content > div:nth-child(8) > div.table-responsive > table > thead > tr:nth-child(1) > th:nth-child(1) > label > input').click();
            document.querySelector('body > div.page-content > div.content-wrapper > div.content > div:nth-child(8) > div:nth-child(1) > div > div > div > button:nth-child(1)').click();
            document.querySelector('#modalConfirmExcel > div > div > div.modal-footer.mt-3 > input').click();
        });
        await new Promise(resolve => setTimeout(resolve, 3000));
        await page.select('#modalConfirmExcel > div > div > div.modal-body.border-bottom > div:nth-child(1) > div.col > select', 'order');
        await new Promise(resolve => setTimeout(resolve, 1000));
        await page.click('#modalConfirmExcel > div > div > div.modal-footer.mt-3 > input');

        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        // await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://trendhunterb2b.com/ko/login.php', { waitUntil: 'networkidle0' });
    await page.type('input[name="user_id"]', username);
    await page.type('input[name="user_pass"]', password);
    await page.click('button[class="btn btn-main btn-lg"]');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}
