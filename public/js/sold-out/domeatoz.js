const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await page.setViewport({
            width: 1920,
            height: 1080
        });
        await page.goto('https://www.domeatoz.com/seller-login', { waitUntil: 'networkidle0' });
        await page.type('#UserId', username);
        await page.type('#UserPass', password);
        await page.click('#loginForm > div.d-flex.align-items-center.justify-content-between.mt-4.mb-0 > button');
        await page.waitForNavigation({ waitUntil: 'load' });
        await page.goto('https://www.domeatoz.com/vendor-myGoods', { waitUntil: 'networkidle0' });
        await page.type('#search', searchStr);
        await page.select('#form > div > div:nth-child(6) > select', '8');
        await page.select('#listBox > div > div:nth-child(1) > select:nth-child(3)', '500');
        await page.click('#form > div > div:nth-child(6) > button.btn.btn-primary.px-5.ms-2');
        await new Promise((page) => setTimeout(page, 3000));
        await page.select('#stateChange', '2');
        let status = await page.evaluate(() => {
            const checkbox = document.querySelector('#allChk');
            if (checkbox) {
                checkbox.click();
                return true;
            } else {
                return false;
            }
        });
        if (status === false) {
            console.log(status);
            return;
        }
        await page.click('#listBox > div > div:nth-child(1) > button.btn.btn-primary.px-3.btn-sm.ms-1');
        await page.waitForSelector('.swal2-popup.swal2-show', { visible: true });
        const confirmBtn = await page.$('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn-danger.swal2-styled');
        if (confirmBtn) {
            await page.click('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn-danger.swal2-styled', { waitUntil: 'networkidle2' });
        } else {
            console.log(false);
            return;
        }
        await page.waitForSelector('.swal2-popup.swal2-show', { visible: true });
        const response = await page.evaluate(() => {
            const resultMessage = document.querySelector('#swal2-content').textContent.trim();
            if (resultMessage.includes('변경 되었습니다.') || resultMessage.includes('품절 처리할 상품이 없습니다')) {
                return true;
            } else {
                return false;
            }
        });
        console.log(response);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
