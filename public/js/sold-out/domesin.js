const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.goto('https://www.domesin.com/scm/login.html', { waitUntil: 'networkidle2' });
        await page.type('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(1) > td > input', username);
        await page.type('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(3) > td > input', password);
        await page.click('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(3) > input[type=image]');
        await page.waitForNavigation();
        await page.goto('https://www.domesin.com/scm/M_item/item_list.html?cate1=&cate2=&cate3=&cate4=&cid=&date=w&start_date=&end_date=&status=&raid=&i_type=&adult=&delivery_type=&isreturn=&tax=&item_sale_type=&ok=&is_overseas=&ls=&q_type=vender_code&rows=20&isort=iid&q=&q2=' + productCode, { waitUntil: 'networkidle2' });
        await page.click('#main > table.tb12 > tbody > tr:nth-child(2) > td:nth-child(1) > div:nth-child(2) > input');
        page.on('dialog', async dialog => {
            await dialog.accept();
            console.log(true);
            return;
        });
        await page.click('#btn_total_sold');
        console.log(response);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
