const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\sellingkok'); // Update your download path

    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://www.sellingkok.com/shop/partner/login.php');
        await page.type('#login_id', username);
        await page.type('#login_pw', password);
        await page.click('#sub-wrapper > div > div.box-block > form > div.foot > button');
        await new Promise((page) => setTimeout(page, 3000));
        await page.goto('https://www.sellingkok.com/shop/partner/?ap=saleitem&status=1', { waitUntil: 'load' });
        await page.click('#list_chk_all');
        page.on('dialog', async dialog => {
            const message = dialog.message();
            if (message.includes('주문내역이 없습니다')) {
                console.log(false);
            }
            await dialog.accept();
            return;
        });
        await page.click('body > div:nth-child(2) > div.page-content > div.well > form > div:nth-child(6) > div.col-sm-3.col-xs-6 > div > a');
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
