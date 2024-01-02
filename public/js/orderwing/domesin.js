const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async () => {
    const downloadPath = path.resolve(__dirname, 'public/assets/excel/orderwing/');

    const browser = await puppeteer.launch({
        headless: false,
        defaultViewport: null,
        args: [
            `--no-sandbox`,
            `--disable-setuid-sandbox`,
            `--disable-dev-shm-usage`,
            `--disable-accelerated-2d-canvas`,
            `--disable-gpu`,
            `--window-size=1920x1080`,
            `--disable-features=site-per-process`,
            `--allow-running-insecure-content`,
            `--enable-features=NetworkService`,
            `--ignore-certificate-errors`,
            `--no-first-run`,
            `--no-zygote`,
            `--single-process`, // <- 이 옵션이 중요합니다. 여러 프로세스가 파일을 쓰지 않도록 함
            `--disable-background-timer-throttling`,
            `--disable-backgrounding-occluded-windows`,
            `--disable-renderer-backgrounding`
        ]
    });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://www.domesin.com/scm/login.html', { waitUntil: 'networkidle2', timeout: 0 });
        const usernameInput = await page.waitForSelector('input[name="m_id"]');
        const passwordInput = await page.waitForSelector('input[name="m_pw"]');
        const signInBtn = await page.waitForSelector('input[type="image"]');
        await usernameInput.type(username);
        await passwordInput.type(password);
        await signInBtn.click();
        await page.waitForNavigation({ timeout: 0 });
        await page.goto('http://domesin.com/scm/M_order/list.html', { waitUntil: 'networkidle2' });
        await page.click('#main > table.tb13 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]');
        // 다운로드 경로 설정
        await page._client.send('Page.setDownloadBehavior', {
            behavior: 'allow',
            downloadPath: downloadPath
        });
        await page.click('#main > div > input.bt_red');
    } catch (error) {
        console.error('Error:', error);
    } finally {
        // await browser.close();
    }
})();
