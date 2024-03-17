const puppeteer = require('puppeteer');
const fs = require('fs');

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

const login = async (page, username, password) => {
    await page.goto('https://www.sellingkok.com/shop/partner/login.php', { waitUntil: 'networkidle0' });
    await page.type('#login_id', username);
    await page.type('#login_pw', password);
    await page.click('#sub-wrapper > div > div.box-block > form > div.foot > button');
    await delay(3000); // Corrected delay usage
};

const setupListeners = (page) => {
    page.on('dialog', async dialog => {
        await dialog.accept();
    });
};

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join('\n');

        await login(page, username, password);

        await page.goto('https://www.sellingkok.com/shop/partner/?ap=list', { waitUntil: 'networkidle0' });
        await page.select('#cd_search', 'pt_it_cd');
        await page.select('#page_num', '100');
        await delay(1000);
        await page.type('textarea[name="it_id"]', searchStr);
        await page.click('body > div:nth-child(2) > div.page-content > div.well > form > div:nth-child(17) > div:nth-child(4) > div > button');

        await delay(3000);

        await page.evaluate(() => {
            const inputElement = document.querySelector('#chkall');
            inputElement?.click();
        });

        setupListeners(page);

        await page.click('body > div:nth-child(2) > div.page-content > form > div:nth-child(11) > div.form-group.pull-left > button:nth-child(2)');
        await delay(3000);
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
