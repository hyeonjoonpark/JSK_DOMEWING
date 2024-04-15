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
        await searchProduct(page, searchStr);
        const processDelProductResult = await processDelProduct(page, browser);
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
async function searchProduct(page, productCode) {
    const searchProductCodeUrl = 'http://www.domesin.com/scm/M_item/item_list.html?cate1=&cate2=&cate3=&cate4=&cid=&date=w&start_date=&end_date=&status=&raid=&i_type=&adult=&delivery_type=&isreturn=&tax=&ls=&ok=&is_overseas=&item_sale_type=&q_type=vender_code&rows=20&isort=iid&q=&q2=' + productCode;
    await page.goto(searchProductCodeUrl, { waitUntil: 'networkidle0' });
}
async function processDelProduct(page, browser) {
    const productExists = await page.evaluate(() => {
        const products = document.querySelectorAll('#main > table > tbody tr');
        if (products.length > 1) {
            return true;
        }
        return false;
    });
    if (productExists === false) {
        return false;
    }
    await page.evaluate(() => {
        document.querySelector('#q_type').value = 'vender_code';
        document.querySelector('#q2').innerHTML = 'CNIMK';
        document.querySelector('#main > form > table > tbody > tr: nth - child(9) > td.cttd > select: nth - child(2)').value = '1000';
        document.querySelector('#main > form > table > tbody > tr:nth-child(9) > td.cttd > input.mybt01-orange').click();
    });
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    await page.evaluate(() => {
        document.querySelector('input[name="ack"]').click();
        document.querySelector('#btn_total_sale').click();
    });


    page.on('dialog', async dialog => {
        const message = dialog.message();
        await dialog.accept();
    });
    const [newPage] = await Promise.all([
        new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
        page.click('#btn_total_sale')
    ]);
    if (newPage) {
        await newPage.waitForSelector('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)');
        const textContent = await newPage.$eval('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)', element => element.textContent);
        if (textContent.includes('판매')) {
            return true;
        }
        return true;
    }
    return false;
}
