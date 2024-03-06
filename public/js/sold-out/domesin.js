const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const pages = await browser.pages();
    const page = pages[0];
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await login(page, username, password);
        await searchProduct(page, productCode);
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
    page.on('dialog', async dialog => {
        await dialog.accept();
        return;
    });
    const productExists = await page.evaluate(() => {
        const productElement = document.querySelector('#main > table > tbody > tr:nth-child(2) > td:nth-child(1) > div:nth-child(2) > input');
        if (productElement) {
            productElement.checked = true;
            return true;
        }
        return false;
    });
    if (productExists === false) {
        return false;
    }
    const [newPage] = await Promise.all([
        new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
        page.click('#btn_total_sold')
    ]);
    if (newPage) {
        await newPage.waitForSelector('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)');
        const textContent = await newPage.$eval('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)', element => element.textContent);
        if (textContent.includes('품절')) {
            return true;
        }
        return false;
    }
    return false;
}