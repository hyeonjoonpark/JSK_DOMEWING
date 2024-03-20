const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');

        await login(page, username, password);

        await page.goto('http://www.tobizon.co.kr/scm/goods/goods_list.php', { waitUntil: 'networkidle0' });
        await page.select('select[name="search_key"]', 'vgoodscd');
        await delay(1000);
        await page.select('select[name="listsize"]', '500');


        await page.type('#area_search_str', searchStr);
        await page.click('#searchFrm > tbody > tr:nth-child(1) > td:nth-child(2) > button');
        await page.waitForNavigation({ waitUntil: 'domcontentloaded' });

        const productElement = await page.$$('#loadWarpGoodslist > table > tbody > tr');//제품이 하나도 없는지 확인
        if (productElement.length < 2) {
            console.log(false);
            return;
        }
        await page.evaluate(() => {
            const inputElement = document.querySelector('#chkAll');
            inputElement?.click();
        });
        // setupListeners(page);
        //여기까지 체크박스까지 처리함
        //팝업 처리 문의
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });

        const [newPage] = await Promise.all([
            new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
            page.click('#fgoodslist > div.local_frm01 > button:nth-child(2)')
        ]);
        if (newPage) {
            await newPage.waitForSelector('#form-memo > div.tbl_frm01.mt-10 > div > input');
            await newPage.click('#form-memo > div.tbl_frm01.mt-10 > div > input');
            await delay(1000);
            await newPage.click('body > div.swal2-container.swal2-center.swal2-backdrop-show.swal2-noanimation > div > div.swal2-actions > button.swal2-confirm.swal2-styled');
        }
        await delay(3000);
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

const login = async (page, username, password) => {
    await page.goto('http://www.tobizon.co.kr/mall/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#mid', username);
    await page.type('#password', password);
    await page.click('#sfrm > div.col-xs-6 > div:nth-child(1) > div.pull-right > button.primary-btn.primary');
    await page.waitForNavigation({ waitUntil: 'load' });
};

const setupListeners = (page) => {
    page.on('dialog', async dialog => {
        await dialog.accept();
    });
};
