const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await login(page, username, password);
        await processPageList(page, searchStr);
        await doSoldOut(page);
        await delay(3000);
        const response = await accessPopup(page);
        console.log(response);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
async function login(page, username, password) {
    await page.goto('https://www.domeatoz.com/seller-login', { waitUntil: 'networkidle0' });
    await page.type('#UserId', username);
    await page.type('#UserPass', password);
    await page.click('#loginForm > div.d-flex.align-items-center.justify-content-between.mt-4.mb-0 > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function processPageList(page, searchStr) {
    await page.goto('https://www.domeatoz.com/vendor-myGoods', { waitUntil: 'networkidle0' });
    await page.evaluate((searchStr) => {
        document.querySelector('#form > div > div:nth-child(6) > select').value = '8';
        document.querySelector('#search').value = searchStr;
    }, searchStr);
    await page.waitForSelector('#listBox > div > div:nth-child(1) > select:nth-child(3)');
    const element = await page.$('#listBox > div > div:nth-child(1) > select:nth-child(3)');
    await element.click();
    await element.select('#listBox > div > div:nth-child(1) > select:nth-child(3)', '500');
    await delay(3000);
    await page.evaluate(() => {
        document.querySelector('#form > div > div:nth-child(6) > button.btn.btn-primary.px-5.ms-2').click();
    });
    await delay(3000);
}
async function doSoldOut(page) {
    const productElement = await page.$$('#list tr');
    if (productElement.length < 1) {
        console.log(false);
        return;
    }
    await page.evaluate(() => {
        const selectElement = document.querySelector('#stateChange');
        selectElement.value = '2';
    });
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
    await page.evaluate(() => {
        const buttonElement = document.querySelector('#listBox > div > div:nth-child(1) > button.btn.btn-primary.px-3.btn-sm.ms-1');
        buttonElement.click();
    });
    await page.waitForSelector('.swal2-popup.swal2-show', { visible: true });
}
async function accessPopup(page) {
    const confirmBtn = await page.$('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn-danger.swal2-styled');
    if (confirmBtn) {
        await page.evaluate(() => {
            const buttonElement = document.querySelector('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn-danger.swal2-styled');
            buttonElement.click();
        });
    } else {
        console.log(false);
        return false;
    }
    await delay(1000);
    await page.waitForSelector('.swal2-popup.swal2-show', { visible: true });
    const response = await page.evaluate(() => {
        const resultMessage = document.querySelector('#swal2-content').textContent.trim();
        if (resultMessage.includes('변경 되었습니다') || resultMessage.includes('품절 처리할 상품이 없습니다')) {
            document.querySelector('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.swal2-styled').click();
            return true;
        } else {
            return false;
        }
    });
    return response;
}
const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));
