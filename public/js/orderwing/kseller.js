const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\kseller'); // Update your download path

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
        await page.goto('http://www.kseller.kr/index.php?vhtml=mb/login_form', { waitUntil: 'networkidle0' });
        await page.click('#t2');//공급사모드로 변경
        await page.type('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(1) > td > input', username);
        await page.type('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(3) > td > input', password);
        await page.click('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(5) > td > input');//로그인
        await page.waitForNavigation({ waitUntil: 'load' });
        await page.goto('http://www.kseller.kr/kpm/shop_order/list.php', { waitUntil: 'networkidle0' });//주문내역 보는 페이지 이동

        await page.select('#content > table.tb11 > tbody > tr:nth-child(4) > td.cttd > select:nth-child(2)', '500');//몇개씩 볼지 설정
        await page.click('#content > table.tb11 > tbody > tr:nth-child(4) > td.cttd > input.bt_blue');
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#content > table.tb13 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]'); //체크박스 클릭
        await page.click('#content > div:nth-child(6) > input:nth-child(5)'); //엑셀 다운로드
        await new Promise((page) => setTimeout(page, 3000));

        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
