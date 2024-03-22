const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\onch3'); // Update your download path

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
        await page.goto('https://www.onch3.co.kr/login/login_web.php', { waitUntil: 'networkidle0' });
        await page.type('body > div > form > input:nth-child(3)', username);
        await page.type('body > div > form > input:nth-child(5)', password);
        await page.click('body > div > form > button');//로그인
        await page.waitForNavigation({ waitUntil: 'load' });


        //
        await page.goto('http://www.tobizon.co.kr/scm/order/order_list.php?ltype=s', { waitUntil: 'networkidle0' });//주문내역 보는 페이지 이동
        await page.select('#searchFrm > tbody > tr:nth-child(1) > td:nth-child(2) > div:nth-child(2) > select', '100');//몇개씩 볼지 설정
        await page.click('#searchFrm > tbody > tr:nth-child(1) > td:nth-child(2) > button');
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#chkAll'); //체크박스 클릭
        await page.click('#loadWarplist > div:nth-child(2) > div > div > button.button.xs.danger'); //엑셀 다운로드
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
