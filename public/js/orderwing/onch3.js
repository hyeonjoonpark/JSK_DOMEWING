const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    const client = await page.target().createCDPSession();


    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\onch3'); // Update your download path

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
        await page.goto('https://www.onch3.co.kr/login/login_web.php', { waitUntil: 'networkidle0' });
        await page.type('body > div > form > div:nth-child(2) > input', username);
        await page.type('body > div > form > div:nth-child(3) > input', password);
        await page.click('body > div > form > button');//로그인
        await page.waitForNavigation({ waitUntil: 'load' });

        await page.goto('https://www.onch3.co.kr/admin_mem_made.php', { waitUntil: 'networkidle0' });//주문내역 보는 페이지 이동
        await page.click('body > center > table > tbody > tr:nth-child(2) > td:nth-child(3) > div.my_pg_con_box > div.my_pg_con > div.excel_down_box > form > div > div:nth-child(2) > a.order_excel_down'); //엑셀 다운로드
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
