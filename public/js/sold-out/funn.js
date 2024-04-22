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
        const productCodesRaw = fs.readFileSync(tempFilePath, 'utf8');
        const productCodes = JSON.parse(productCodesRaw);
        const modifiedProductCodes = productCodes.map(code => "JSKR" + code);
        const searchStr = modifiedProductCodes.join(' ');
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
    await page.goto('https://scm.funn.co.kr/login/login.asp', { waitUntil: 'networkidle0' });
    await page.type('body > form > div > div:nth-child(3) > div > div:nth-child(1) > input[type=text]', username);
    await page.type('body > form > div > div:nth-child(3) > div > div:nth-child(2) > input[type=password]', password);
    await page.click('body > form > div > div:nth-child(3) > span > input[type=submit]');//로그인
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function processPageList(page, searchStr) {
    await page.goto('https://goods.funn.co.kr/goods/scm/goods_List.asp?openmenu=b03m00s00&Dealer=sungil2018', { waitUntil: 'networkidle0', timeout: 0 });
    await page.select('#sch > table > tbody > tr:nth-child(9) > td:nth-child(2) > select', 'VenderPCode');
    await delay(1000);
    await page.type('#sch > table > tbody > tr:nth-child(9) > td:nth-child(2) > input[type=text]', searchStr);
    await delay(1000);
    await page.select('#info > div.sort > select', '500');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
}

async function doSoldOut(page) {
    await page.click('#goods > table > thead > tr > td:nth-child(1) > input[type=checkbox]');
    await delay(1000);
    await page.click('#control > div.left > a:nth-child(3) > span');
    await delay(3000);

}

async function clearPopup(page) {
    page.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('상품을 품절')) {
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
