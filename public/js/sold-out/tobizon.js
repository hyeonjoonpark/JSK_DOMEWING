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

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

async function login(page, username, password) {
    await page.goto('http://www.tobizon.co.kr/mall/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#mid', username);
    await page.type('#password', password);
    await page.click('#sfrm > div.col-xs-6 > div:nth-child(1) > div.pull-right > button.primary-btn.primary');
    await page.waitForNavigation({ waitUntil: 'load' });
};

async function processPageList(page, searchStr) {
    await page.goto('http://www.tobizon.co.kr/scm/goods/goods_list.php', { waitUntil: 'networkidle0' });
    await page.select('select[name="search_key"]', 'vgoodscd');
    await delay(1000);
    await page.select('select[name="listsize"]', '500');
    await page.type('#area_search_str', searchStr);
    await delay(1000);
    await page.click('#searchFrm > tbody > tr:nth-child(1) > td:nth-child(2) > button');
    await delay(1000);
}

async function doSoldOut(page) {
    const productElement = await page.$$('#loadWarpGoodslist > table > tbody > tr');
    if (productElement.length < 2) {
        console.log(false);
        return;
    }
    await delay(1000);
    await page.evaluate(() => {
        const inputElement = document.querySelector('#chkAll');
        inputElement?.click();
    });
    await delay(1000);
    await page.click('#loadWarpGoodslist > div:nth-child(2) > div > div > button.button.warning.xs');
    console.log(true);
    return;
}


async function clearPopup(page) {
    page.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('품절 처리')) {
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
