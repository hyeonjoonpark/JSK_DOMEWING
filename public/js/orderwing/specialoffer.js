const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\specialoffer'); // Update your download path

    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://specialoffer.kr/bbs/login.php?url=/mypage/page.php?code=seller_main');
        await page.type('#login_id', username);
        await page.type('#login_pw', password);
        await page.click('#login_fld > dl > dd:nth-child(5) > button');
        await page.waitForNavigation({ waitUntil: 'load' });
        await page.goto('https://specialoffer.kr/mypage/page.php?code=seller_odr_2', { waitUntil: 'networkidle0' });
        await page.select('#page_rows', '150');
        await new Promise((page) => setTimeout(page, 3000));
        page.on('dialog', async dialog => {
            const message = dialog.message();
            if (message.includes('없습니다')) {
                console.log(false);
            }
            await dialog.accept();
            return;
        });
        await page.click('#forderlist > div.local_frm02 > a:nth-child(2)');
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
