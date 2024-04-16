const puppeteer = require('puppeteer');
const fs = require('fs');



async function main() {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    clearPopup(page);
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await login(page, username, password);
        await processPageList(page, searchStr);
        await doSoldOut(page);

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
}

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
    await page.click('input[value="검색"]');
    await delay(5000);
}
async function doSoldOut(page) {
    const allCheckInput = await page.$('input[name="_checked"]');
    if (!allCheckInput) {
        console.log(false);
        return;
    }
    await allCheckInput.click();
    await delay(1000);

    await page.select('#lList > div.pFunctions > select', 'N');


    const buttonSelector = await page.waitForSelector('#lList > div.pFunctions > a:nth-child(4)');
    await buttonSelector.click();
    await delay(5000);
}

const delay = (time) => new Promise((resolve) => setTimeout(resolve, time));

const navigateAndWait = async (page, url, waitUntil = 'networkidle0') => {
    await page.goto(url, { waitUntil });
};

main().catch(console.error);

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

