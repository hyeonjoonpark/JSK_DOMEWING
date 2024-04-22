const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    clearPopup(page);
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await login(page, username, password);
        await processPageList(page, searchStr);
        await doSoldOut(page);
        await delay(3000);
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

async function processPageList(page, searchStr) {
    await page.goto('http://www.domero.net/vms/shop_item/item_list.php', { waitUntil: 'networkidle0', timeout: 0 });
    await page.select('#q_type', 'vender_code');
    await delay(2000);
    await page.select('select[name="rows"]', '100');
    await delay(1000);
    await page.type('#q2', searchStr);
    await page.click('#content > table.tb11 > tbody > tr:nth-child(8) > td.cttd > input.bt_blue');
    await delay(3000);
}

async function doSoldOut(page) {
    await page.click('input[name="ack"]');
    await delay(1000);
    await page.click('#btn_total_sold');
    const [newPage] = await Promise.all([
        new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
        page.click('#btn_total_sold')
    ]);
    await newPage.waitForNavigation({ waitUntil: 'load' });
}

async function clearPopup(page) {
    page.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('일괄 품절')) {
            await dialog.accept();
            console.log(true);
        }
        else {
            await dialog.dismiss();
            console.log(false);
        }
        return;
    });
}


const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));
