const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        const searchStr = productCodes.join(',');

        await login(page, username, password);

        await page.goto('http://www.tobizon.co.kr/scm/goods/goods_list.php', { waitUntil: 'networkidle0' });
        await page.select('select[name="search_key"]', 'vgoodscd');
        await delay(1000);
        await page.select('select[name="listsize"]', '500');
        await page.type('#area_search_str', searchStr);
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#searchFrm > tbody > tr:nth-child(1) > td:nth-child(2) > button');
        await new Promise((page) => setTimeout(page, 1000));
        const productElement = await page.$$('#loadWarpGoodslist > table > tbody > tr');
        if (productElement.length < 2) {
            console.log(false);
            return;
        }
        await new Promise((page) => setTimeout(page, 1000));
        await page.evaluate(() => {
            const inputElement = document.querySelector('#chkAll');
            inputElement?.click();
        });
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#loadWarpGoodslist > div:nth-child(2) > div > div > button.button.warning.xs');
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

const login = async (page, username, password) => {
    await page.goto('https://www.onch3.co.kr/login/login_web.php', { waitUntil: 'networkidle0' });
    await page.type('body > div > form > input:nth-child(3)', username);
    await page.type('body > div > form > input:nth-child(5)', password);
    await page.click('body > div > form > button');//로그인
    await page.waitForNavigation({ waitUntil: 'load' });
};
