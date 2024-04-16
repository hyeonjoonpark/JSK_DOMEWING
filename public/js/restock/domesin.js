
const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const pages = await browser.pages();
    const page = pages[0];
    await page.setViewport({
        width: 1920,
        height: 1080
    });

    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await login(page, username, password);
        const processDelProductResult = await processDelProduct(page, browser, searchStr);
        console.log(processDelProductResult);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
async function login(page, username, password) {
    await page.goto('https://www.domesin.com/scm/login.html', { waitUntil: 'networkidle0' });
    await page.type('input[name="m_id"]', username);
    await page.type('input[name="m_pw"]', password);
    await page.click('input[type="image"]');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function processDelProduct(page, browser, productCode) {
    const itemListingUrl = 'http://www.domesin.com/scm/M_item/item_list.html';
    await page.goto(itemListingUrl, { waitUntil: 'networkidle0' });

    await page.evaluate((code) => {
        document.querySelector('#q_type').value = 'vender_code';
        document.querySelector('#main > form > table > tbody > tr:nth-child(9) > td.cttd > select:nth-child(2)').value = '1000';
        document.querySelector('#q2').value = code;
        document.querySelector('#main > form > table > tbody > tr:nth-child(9) > td.cttd > input.mybt01-orange').click();
    }, productCode);

    await page.waitForNavigation({ waitUntil: 'networkidle0' });

    page.on('dialog', async dialog => {
        await dialog.accept();
    });
    const productElement = await page.$$('#main > table > tbody > tr');
    if (productElement.length < 2) {
        console.log(false);
        return;
    }

    const newPagePromise = new Promise(resolve => browser.once('targetcreated', target => resolve(target.page())));

    await page.evaluate(() => {
        document.querySelector('input[name="ack"]').click();
        document.querySelector('#btn_total_sale').click();
    });

    const newPage = await newPagePromise;

    if (newPage) {
        await newPage.waitForSelector('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)');
        const textContent = await newPage.$eval('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)', element => element.textContent);
        await newPage.click("body > table > tbody > tr:nth-child(3) > td > input");

        return textContent.includes('판매');
    }

    return false;
}
