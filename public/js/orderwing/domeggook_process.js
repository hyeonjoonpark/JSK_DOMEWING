const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://domeggook.com/sc/?login=pc', { waitUntil: 'networkidle2' });
        await page.type('#idInput', username);
        await page.type('#pwInput', password);
        await page.click('#formLogin > input.formSubmit');
        await page.waitForNavigation();
        await page.goto('https://domeggook.com/sc/order/lstInprocess', { waitUntil: 'networkidle2' });
        await page.click('#lGrid > div > div.tui-grid-content-area > div.tui-grid-lside-area > div.tui-grid-header-area > table > tbody > tr > th.tui-grid-cell.tui-grid-cell-header.tui-grid-cell-row-header > span > input[type=checkbox]');
        await page.click('#lList > div.pHeader > form > a');
        // Wait for the iframe to load and then perform actions inside the iframe
        await new Promise((page) => setTimeout(page, 3000));
        const frameElement = await page.waitForSelector('#gLayerFrame > div > iframe');
        const frame = await frameElement.contentFrame();
        await frame.click('#lXlsReqNoticeBtnSubmit');
        await new Promise((page) => setTimeout(page, 3000));
        await frame.click('#lXlsReqNoticeBtnClose');
        await new Promise((page) => setTimeout(page, 70000));
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
