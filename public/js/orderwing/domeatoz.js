const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const client = await page.target().createCDPSession();

    const downloadPath = path.resolve('C:\\xampp\\htdocs\\sellwing\\public\\assets\\excel\\orderwing\\domeatoz'); // Update your download path

    // Setting up Chrome to allow downloads
    await client.send('Page.setDownloadBehavior', {
        behavior: 'allow',
        downloadPath: downloadPath,
    });
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.setViewport({
            width: 1920,
            height: 1080
        });
        await page.goto('https://www.domeatoz.com/seller-login', { waitUntil: 'networkidle0' });
        await page.type('#UserId', username);
        await page.type('#UserPass', password);
        await page.click('#loginForm > div.d-flex.align-items-center.justify-content-between.mt-4.mb-0 > button');
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('https://www.domeatoz.com/vendor-order', { waitUntil: 'domcontentloaded' });
        await page.select('#listBox > div > div.col-md-4.w-100.d-flex.mb-3 > select:nth-child(1)', '300');
        await new Promise((page) => setTimeout(page, 3000));
        await page.click('#orderIngType1');
        await new Promise((page) => setTimeout(page, 3000));
        await page.click('#listBox > div > div.dataTable-wrapper.dataTable-loading.no-footer.sortable.searchable.fixed-columns > div > table > thead > tr > th.text-center.align-middle.p-0 > input');
        await page.click('#orderSheetDown');
        await page.click('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn-danger.swal2-styled');
        await new Promise((page) => setTimeout(page, 3000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
