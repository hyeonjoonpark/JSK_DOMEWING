const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await login(page, username, password);
        const products = await processPageList(page, searchStr);
        if (products === false) {
            console.log(false);
            return;
        }
        for (const product of products) {
            await validateProducts(product);
        }
        // 마침내 품절/재입고 버튼 누르기.
        await new Promise((page) => setTimeout(page, 1000));
        page.on('dialog', async dialog => {
            const message = dialog.message();
            await dialog.accept();
            if (message.includes('재입고') || message.includes('정상적으로 수정') || message.includes("일괄")) {
                console.log(true);
                return;
            }
        });
        await page.click('body > table:nth-child(1) > tbody > tr:nth-child(6) > td > table > tbody > tr:nth-child(3) > td > table > tbody > tr > td:nth-child(3) > table > tbody > tr > td > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table:nth-child(2) > tbody > tr:nth-child(2) > td > table > tbody > tr:nth-child(2) > td:nth-child(1) > div:nth-child(2) > input[type=button]:nth-child(1)');
        await new Promise((page) => setTimeout(page, 3000));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
async function validateProducts(product) {
    const productStatus = await product.evaluate(() => {
        const productStatus = document.querySelector('td:nth-child(9) > span').textContent.trim();
        if (productStatus.includes('품절')) {
            return true;
        }
        return false;
    });
    if (productStatus === true) {
        const productCheckbox = await product.$('input[type=checkbox]');
        await productCheckbox.click();
    }
}
async function login(page, username, password) {
    await page.goto('https://ownerclan.com/vender/', { waitUntil: 'networkidle0' });
    const frame = page.frames().find(f => f.name() === 'vmainframe');
    await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(1) > input', username);
    await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(2) > input', password);
    await frame.evaluate(() => {
        document.querySelector("body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(3) > input[type=submit]").click();
    });
    await frame.waitForNavigation({ waitUntil: 'networkidle0' });
}
async function processPageList(page, searchStr) {
    await page.goto('https://ownerclan.com/vender/product_myprd.php', { waitUntil: 'domcontentloaded' });
    await page.select('select[name="s_check"]', 'vcode');
    await page.evaluate(() => {
        const closeBtn = document.querySelector('#Notice10 > table > tbody > tr:nth-child(2) > td > div');
        if (closeBtn) {
            closeBtn.click();
        }
    });
    await page.click('#idx_saletype1');
    await page.type('input[name="search"]', searchStr);
    await page.select('select[name="display_count"]', '500');
    await new Promise((page) => setTimeout(page, 5000));
    const products = await page.$$('tr[height="40"]');
    if (products.length < 1) {
        return false;
    }
    return products;
}
