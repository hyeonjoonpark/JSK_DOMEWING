const puppeteer = require('puppeteer');
const fs = require('fs');
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

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

const login = async (page, username, password) => {
    await page.goto('http://www.kseller.kr/index.php?vhtml=mb/login_form', { waitUntil: 'networkidle0' });
    await page.click('#t2');//공급사모드로 변경
    await page.type('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(1) > td > input', username);
    await page.type('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(3) > td > input', password);
    await page.click('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(5) > td > input');//로그인
    await page.waitForNavigation({ waitUntil: 'load' });
};

async function processPageList(page, searchStr) {
    await page.goto('http://www.kseller.kr/kpm/shop_item/item_list.php', { waitUntil: 'networkidle0' });
    await page.select('#q_type', 'vender_code'); //상품코드
    await delay(1000);
    await page.select('#content > table.tb11 > tbody > tr:nth-child(8) > td.cttd > select:nth-child(2)', '500');//한번에 보는 갯수
    await page.type('#q2', searchStr); //입력창에 입력
    await delay(1000);
    await page.click('#content > table.tb11 > tbody > tr:nth-child(8) > td.cttd > input.bt_blue');//검색버튼 클릭
    await delay(1000);
}

async function doSoldOut(page) {
    const productElement = await page.$$('#content > table.tb12 > tbody > tr');
    if (productElement.length < 2) {
        console.log(false);
        return;
    }
    await delay(1000);
    await page.evaluate(() => {
        const inputElement = document.querySelector('#content > table.tb12 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]');
        inputElement?.click();
    });
    await page.click('#btn_total_sold');
    await delay(2000);
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
