const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
        await doSoldOut(page, browser);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

async function login(page, username, password) {
    await page.goto('https://specialoffer.kr/bbs/login.php?url=/mypage/page.php?code=seller_main', { waitUntil: 'networkidle0' });
    await page.type('#login_id', username);
    await page.type('#login_pw', password);
    await page.click('#login_fld > dl > dd:nth-child(5) > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function processPageList(page, searchStr) {
    await page.goto('https://specialoffer.kr/mypage/page.php?code=seller_goods_change', { waitUntil: 'networkidle0' });
    await page.select('select[name="sfl"]', 'seller_gcode');
    await delay(1000);
    await page.type('input[name="stx"]', searchStr);
    await page.click('input[value="검색"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
}

async function doSoldOut(page, browser) {
    const productElement = await page.$$('tr.list0');
    if (productElement.length < 1) {
        console.log(false);
        return;
    }
    await page.evaluate(() => {
        const inputElement = document.querySelector('#sodr_list > thead > tr:nth-child(1) > th:nth-child(1) > input');
        inputElement?.click();
    });
    const [newPage] = await Promise.all([
        new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
        page.click('#fgoodslist > div.local_frm01 > button:nth-child(2)')
    ]);
    if (newPage) {
        await newPage.waitForSelector('input[value="등록"]');
        await newPage.click('input[value="등록"]');
        await delay(1000);
        await newPage.click('body > div.swal2-container.swal2-center.swal2-backdrop-show.swal2-noanimation > div > div.swal2-actions > button.swal2-confirm.swal2-styled');
    }
    await delay(2000);
    console.log(true);
    return;

}

