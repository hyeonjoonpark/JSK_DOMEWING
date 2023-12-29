const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.setViewport({
            width: 1920,
            height: 1080
        });
        await page.goto('https://www.domeatoz.com/seller-login', { waitUntil: 'networkidle2' });
        await page.type('#UserId', username);
        await page.type('#UserPass', password);
        await page.click('#loginForm > div.d-flex.align-items-center.justify-content-between.mt-4.mb-0 > button');
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('https://www.domeatoz.com/vendor-myGoods', { waitUntil: 'networkidle2' });
        await page.type('#search', productCode);
        await page.select('#form > div > div:nth-child(6) > select', '8');
        await page.click('#forSaleType1');
        await page.click('#form > div > div:nth-child(6) > button.btn.btn-primary.px-5.ms-2');
        await new Promise((page) => setTimeout(page, 3000));
        await page.select('#stateChange', '1');
        await page.click('#list > tr:first-child > td.text-center.align-middle.p-1 > input');
        await page.click('#listBox > div > div:nth-child(1) > button.btn.btn-primary.px-3.btn-sm.ms-1');
        await page.waitForSelector('.swal2-popup.swal2-show', { visible: true });
        await page.click('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn-danger.swal2-styled', { waitUntil: 'networkidle2' });
        await page.waitForSelector('.swal2-popup.swal2-show', { visible: true });
        const response = await page.evaluate(() => {
            const resultMessage = document.querySelector('#swal2-content').textContent.trim();
            if (resultMessage.includes('변경 되었습니다.')) {
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
