const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.goto('https://ownerclan.com/vender/', { waitUntil: 'networkidle2' });
        const frame = page.frames().find(f => f.name() === 'vmainframe');
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(1) > input', username);
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(2) > input', password);
        await frame.evaluate(() => {
            document.querySelector("body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(3) > input[type=submit]").click();
        });
        await frame.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('https://ownerclan.com/vender/product_myprd.php');
        await page.select('#sForm > table > tbody > tr:nth-child(7) > td > table > tbody > tr > td > select', 'vcode');
        await page.evaluate(() => {
            const closeBtn = document.querySelector('#Notice10 > table > tbody > tr:nth-child(2) > td > div');
            if (closeBtn) {
                closeBtn.click();
            }
        });
        await page.click('#idx_saletype1');
        await page.click('#idx_statustype1');
        await page.type('#sForm > table > tbody > tr:nth-child(7) > td > table > tbody > tr > td > input[type=text]:nth-child(2)', productCode);
        await page.click('#sForm > table > tbody > tr:nth-child(7) > td > table > tbody > tr > td > a');
        await new Promise((page) => setTimeout(page, 3000));
        const checkboxSelector = 'body > table:nth-child(1) > tbody > tr:nth-child(6) > td > table > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(3) > table > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table:nth-child(2) > tbody > tr:nth-child(5) > td > table > tbody > tr:nth-child(2) > td:nth-child(1) > input[type=checkbox]';
        // 요소가 있는지 확인합니다.
        const checkbox = await page.$(checkboxSelector);
        if (checkbox) {
            await checkbox.click(); // 요소가 있으면 클릭
        } else {
            console.log(false);
            return;
        }
        page.on('dialog', async dialog => {
            const message = dialog.message();
            await dialog.accept();
            if (message.includes('이미 품절') || message.includes('정상적으로 수정')) {
                console.log(true);
            }
            return;
        });
        await page.click('body > table:nth-child(1) > tbody > tr:nth-child(6) > td > table > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(3) > table > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table:nth-child(2) > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(2) > td:nth-child(1) > div:nth-child(2) > input[type=button]:nth-child(2)');
        await new Promise((page) => setTimeout(page, 3000));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
