const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.goto('https://specialoffer.kr/bbs/login.php?url=/mypage/page.php?code=seller_main', { waitUntil: 'networkidle0' });
        await page.type('#login_id', username);
        await page.type('#login_pw', password);
        await page.click('#login_fld > dl > dd:nth-child(5) > button');
        await page.waitForNavigation({ waitUntil: 'load' });
        await page.goto('https://specialoffer.kr/mypage/page.php?code=seller_goods_change', { waitUntil: 'domcontentloaded' });
        await page.select('select[name="sfl"]', 'seller_gcode');
        await delay(1000);
        await page.type('input[name="stx"]', productCode);
        await page.click('input[value="검색"]');
        await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
        await page.evaluate(() => {
            const inputElement = document.querySelector('input[name="checkall"]');
            inputElement?.click();
        });
        await delay(1000);
        const product = await page.$('tr.list0');
        if (!product) {
            console.log(false);
            return;
        }
        const [newPage] = await Promise.all([
            new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
            page.click('#fgoodslist > div.local_frm01 > button:nth-child(4)')
        ]);
        if (newPage) {
            await delay(1000);
            await newPage.click('#form-memo > div:nth-child(5) > div > input');
            await delay(1000);
            await newPage.click('body > div.swal2-container.swal2-center.swal2-backdrop-show.swal2-noanimation > div > div.swal2-actions > button.swal2-confirm.swal2-styled');
            await delay(3000);
            console.log(true);
            return;
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));
