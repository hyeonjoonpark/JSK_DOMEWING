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
        const searchStr = productCodes.join('\n');
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
    await page.goto('https://www.sellingkok.com/shop/partner/login.php', { waitUntil: 'networkidle0' });
    await page.type('#login_id', username);
    await page.type('#login_pw', password);
    await page.click('#sub-wrapper > div > div.box-block > form > div.foot > button');
    await delay(3000);
};

async function processPageList(page, searchStr) {
    await page.goto('https://www.sellingkok.com/shop/partner/?ap=list', { waitUntil: 'networkidle0' });
    await page.select('#cd_search', 'pt_it_cd');
    await page.type('textarea[name="it_id"]', searchStr);
    await page.select('#page_num', '500');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
}
async function doSoldOut(page) {
    await page.evaluate(() => {
        const inputElement = document.querySelector('#chkall');
        inputElement?.click();
    });
    await page.click('body > div:nth-child(2) > div.page-content > form > div:nth-child(11) > div.form-group.pull-left > button:nth-child(2)');
    await delay(3000);
}

async function clearPopup(page) {
    page.on('dialog', async dialog => {
        const message = dialog.message();
        await dialog.accept();
        if (message.includes('수정이 완료')) {
            console.log(true);
        }
        else {
            console.log(false);
        }
        return;
    });
}





