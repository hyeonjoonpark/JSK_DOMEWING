const puppeteer = require('puppeteer');

const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));

const navigateAndWait = async (page, url, waitUntil = 'networkidle0') => {
    await page.goto(url, { waitUntil });
};

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    await page.setViewport({
        width: 1920,
        height: 1080
    });

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
    await navigateAndWait(page, 'https://domeggook.com/sc/?login=pc');
    await page.type('#idInput', username);
    await page.type('#pwInput', password);
    await page.click('#formLogin > input.formSubmit');
    await page.waitForNavigation();
}
async function processPageList(page, searchStr) {
    await navigateAndWait(page, 'https://domeggook.com/sc/item/lstAll');
    await page.click('input[value="code"]');
    await page.type('textarea[name="nos"]', searchStr);
    await page.select('select[name="sz"]', '500');
    await page.click('input[value="검색"]');
    await delay(5000);
}

async function doRestock(page) {
    const checkboxSelector = await page.waitForSelector('#lGrid > div > div.tui-grid-content-area > div.tui-grid-lside-area > div.tui-grid-body-area > div > div.tui-grid-table-container > table > tbody > tr:nth-child(1) > td.tui-grid-cell.tui-grid-cell-has-input.tui-grid-cell-row-header > div > input[type=checkbox]');
    const selectSelector = await page.waitForSelector('#lList > div.pFunctions > select');
    const buttonSelector = await page.waitForSelector('#lList > div.pFunctions > a:nth-child(4)');
    await checkboxSelector.click();
    await selectSelector.select('Y');
    page.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('상품수정이 모두 완료')) {
            console.log(true);
        }
        await dialog.accept();
        return;
    });
    await buttonSelector.click();
    await new Promise((page) => setTimeout(page, 3000));
}
