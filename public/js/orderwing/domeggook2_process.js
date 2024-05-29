const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    clearPopup(page);
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://domeggook.com/sc/?login=pc', { waitUntil: 'networkidle2' });
        await page.type('#idInput', username);
        await page.type('#pwInput', password);
        await page.click('#formLogin > input.formSubmit');
        await page.waitForNavigation();
        await page.goto('https://domeggook.com/sc/order/lstInprocess', { waitUntil: 'networkidle2' });
        const data = await page.$('#lGrid > div > div.tui-grid-content-area > div.tui-grid-lside-area > div.tui-grid-body-area > div > div.tui-grid-table-container > table > tbody > tr');
        if (!data) {
            return false;
        }
        const elements = await page.$$('#lGrid > div > div.tui-grid-content-area > div.tui-grid-lside-area > div.tui-grid-body-area > div > div.tui-grid-table-container > table > tbody tr');
        if (elements.length < 1) {
            return false;
        }
        await page.click('#lGrid > div > div.tui-grid-content-area > div.tui-grid-lside-area > div.tui-grid-header-area > table > tbody > tr > th.tui-grid-cell.tui-grid-cell-header.tui-grid-cell-row-header > span > input[type=checkbox]');
        await page.click('#lList > div.pHeader > form > a');
        await new Promise((page) => setTimeout(page, 3000));
        const frameElement = await page.waitForSelector('#gLayerFrame > div > iframe');
        const frame = await frameElement.contentFrame();
        await frame.click('#lXlsReqNoticeBtnSubmit');
        await new Promise((page) => setTimeout(page, 3000));
        await frame.click('#lXlsReqNoticeBtnClose');
        await new Promise((page) => setTimeout(page, 70000));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
async function clearPopup(page) {
    page.on('dialog', async dialog => {
        await dialog.accept();
        await new Promise((page) => setTimeout(page, 1000));
        return false;
    });
}
