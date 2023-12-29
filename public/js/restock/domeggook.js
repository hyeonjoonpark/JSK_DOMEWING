const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.setViewport({
            width: 1920,
            height: 1080,
            deviceScaleFactor: 1
        });
        await page.goto('https://domeggook.com/sc/?login=pc', { waitUntil: 'networkidle2' });
        await page.type('#idInput', username);
        await page.type('#pwInput', password);
        await page.click('#formLogin > input.formSubmit');
        await page.waitForNavigation();
        await page.goto('https://domeggook.com/sc/item/lstAll', { waitUntil: 'networkidle2' });
        await page.click('input[value="code"]');
        await page.type('textarea[name="nos"]', productCode);
        await page.click('input[value="검색"]');
        await new Promise((page) => setTimeout(page, 3000));
        const selectSelector = await page.waitForSelector('#lList > div.pFunctions > select');
        await page.evaluate(() => {
            document.querySelector("#lGrid > div > div.tui-grid-content-area > div.tui-grid-lside-area > div.tui-grid-body-area > div > div.tui-grid-table-container > table > tbody > tr:nth-child(1) > td.tui-grid-cell.tui-grid-cell-has-input.tui-grid-cell-row-header > div > input[type=checkbox]").checked = true;
        });
        await selectSelector.select('Y');
        page.on('dialog', async dialog => {
            const message = dialog.message();
            if (message.includes('완료')) {
                console.log(true);
            }
            await dialog.accept();
            return;
        });
        await page.evaluate(() => {
            document.querySelector("#lList > div.pFunctions > a:nth-child(4)").click();
        });
    } catch (error) {
        console.error('Error:', error);
    } finally {
        // await browser.close();
    }
})();
