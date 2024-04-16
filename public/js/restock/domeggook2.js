const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.

const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));

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
        await doRestock(page);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://domeggook.com/sc/?login=pc', { waitUntil: 'networkidle0' });
    await page.type('#idInput', username);
    await page.type('#pwInput', password);
    await page.click('#formLogin > input.formSubmit');
    await page.waitForNavigation();
}
async function processPageList(page, searchStr) {
    await page.goto('https://domeggook.com/sc/item/lstAll', { waitUntil: 'networkidle0' });
    await page.click('input[value="code"]');
    await page.type('textarea[name="nos"]', searchStr);
    await page.click('input[value="검색"]');
    await delay(5000);
}

async function doRestock(page) {
    const checkboxSelector = await page.waitForSelector('input[name="_checked"]');
    const selectSelector = await page.waitForSelector('#lList > div.pFunctions > select');
    const buttonSelector = await page.waitForSelector('#lList > div.pFunctions > a:nth-child(4)');
    await checkboxSelector.click();
    await selectSelector.select('Y');
    await buttonSelector.click();
    await new Promise((page) => setTimeout(page, 3000));
}

async function clearPopup(page) {
    page.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('수정를 하시겠습니까')) {
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
