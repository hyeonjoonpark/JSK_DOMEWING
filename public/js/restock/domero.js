const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await login(page, username, password);
        await processPageList(page, productCode);
        await doRestock(page);



    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://www.domero.net/index.php?PAGECODE=member/login_form&loginmode=v', { waitUntil: 'networkidle0', timeout: 0 });
    await page.type('input[name="m_id"]', username);
    await page.type('input[name="m_pw"]', password);
    await page.click('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(5) > td > input');
    await page.waitForNavigation();
}

async function processPageList(page, productCode) {
    await page.goto('http://www.domero.net/vms/shop_item/item_list.php', { waitUntil: 'networkidle2', timeout: 0 });
    await page.select('#q_type', 'vender_code');
    await new Promise((page) => setTimeout(page, 3000));
    await page.select('select[name="rows"]', '100');
    await new Promise((page) => setTimeout(page, 1000));
    await page.type('#q2', productCode);
    await page.click('#content > table.tb11 > tbody > tr:nth-child(8) > td.cttd > input.bt_blue');
    await new Promise((page) => setTimeout(page, 3000));
}

async function doRestock(page) {
    await page.click('#content > table.tb12 > tbody > tr > td:nth-child(1) > input[type=checkbox]');
    page.on('dialog', async dialog => {
        await dialog.accept();
        console.log(true);
        return;
    });
    await page.click('#btn_total_sale');
    await new Promise((page) => setTimeout(page, 3000));
}
