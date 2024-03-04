const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await login(page, username, password);
        await page.goto('https://www.sellingkok.com/shop/partner/?ap=list', { waitUntil: 'networkidle0' });
        await page.select('#cd_search', 'pt_it_cd');
        await page.type('body > div:nth-child(2) > div.page-content > div.well > form > div:nth-child(16) > div.col-sm-4.col-xs-6 > div > textarea', productCode);
        await page.click('body > div:nth-child(2) > div.page-content > div.well > form > div:nth-child(17) > div:nth-child(4) > div > button');
        await new Promise((page) => setTimeout(page, 3000));
        await page.evaluate(() => {
            const inputElement = document.querySelector('#chk_0');
            if (inputElement) {
                inputElement.click();
            } else {
                console.log(false);
            }
        });
        page.on('dialog', async dialog => {
            const message = dialog.message();
            await dialog.accept();
            if (message.includes('수정할 상품을 선택해 주세요')) {
                console.log(false);
            }
            if (message.includes('완료')) {
                console.log(true);
            }
        });
        await page.click('body > div:nth-child(2) > div.page-content > form > div:nth-child(11) > div.form-group.pull-left > button:nth-child(2)');
        await new Promise((page) => setTimeout(page, 3000));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
async function login(page, username, password) {
    await page.goto('https://www.sellingkok.com/shop/partner/login.php', { waitUntil: 'networkidle0' });
    await page.type('#login_id', username);
    await page.type('#login_pw', password);
    await page.click('#sub-wrapper > div > div.box-block > form > div.foot > button');
    await new Promise((page) => setTimeout(page, 3000));
}