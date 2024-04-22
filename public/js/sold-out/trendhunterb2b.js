const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    clearPopup(page);
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const username = 'jskorea2024';
        // const password = 'tjddlf88!@';
        // const productCodes = ['Q6PEA'];
        const searchStr = productCodes.join(',');

        await login(page, username, password);
        await processPageList(page, searchStr);

        await doSoldOut(page, browser);
        await delay(3000);

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

async function login(page, username, password) {
    await page.goto('https://trendhunterb2b.com/ko/login.php', { waitUntil: 'networkidle0' });
    await page.type('input[name="user_id"]', username);
    await page.type('input[name="user_pass"]', password);
    await page.click('button[class="btn btn-main btn-lg"]');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function processPageList(page, searchStr) {
    await page.goto('https://trendhunterb2b.com/ko/provider/shop-products.php', { waitUntil: 'networkidle0' });
    await page.evaluate((searchStr) => {
        document.querySelector('input[value="sp.code_seller"]').click();
        document.querySelector('textarea[class="form-control col-md-6 d-inline-block"]').value = searchStr;
        document.querySelector('#searchForm > div.form-group.row.mb-2.mt-3 > div > button').click();
    }, searchStr);
    await delay(3000);
}

async function doSoldOut(page, browser) {
    const productElement = await page.$$('body > div.page-content > div.content-wrapper > div.content > div:nth-child(6) > div.table-responsive.stickyHeadTable > table > tbody tr');
    if (productElement.length < 1) {
        console.log(false);
        return;
    }


    await page.evaluate(async () => {
        document.querySelector('#checkAll').click();
        await new Promise(resolve => setTimeout(resolve, 2000));
        document.querySelector('select[name="change_mode"]').value = 'status';
        await new Promise(resolve => setTimeout(resolve, 2000));
        document.querySelector('button[id="btnChangeAll"]').click();

    });

    const newPagePromise = new Promise(x => browser.once('targetcreated', target => x(target.page())));
    const newPage = await newPagePromise;
    newPage.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('수정')) {
            await dialog.accept();
            console.log(true);
        } else console.log(false);
        return;
    });
    await new Promise(resolve => setTimeout(resolve, 2000));
    if (newPage) {
        await newPage.click('body > div > div > div > form > div > div.card-body > div > div > div > div:nth-child(1) > label');
        await new Promise(resolve => setTimeout(resolve, 1000));
        await newPage.click('body > div > div > div > form > div > div.card-header.header-elements-inline.bg-dark > div > button');

    }
    await delay(2000);
    console.log(true);
    return;
}


async function clearPopup(page) {
    page.on('dialog', async dialog => {
        const message = dialog.message();
        if (message.includes('상품을 선택')) {
            await dialog.dismiss();
            console.log(false);
        }
        else {
            await dialog.accept();
            console.log(true);
        }
        return;
    });
}
