const puppeteer = require('puppeteer');
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [username, password, tempFilePath] = process.argv.slice(2);
        const productCodes = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const searchStr = productCodes.join(',');
        await page.goto('https://www.wholesaledepot.co.kr/wms/member/login.php', { waitUntil: 'networkidle0' });
        await page.type('#id', username);
        await page.type('#passwd', password);
        await page.click('#frm > div > div > button');
        await page.waitForNavigation({ waitUntil: 'load' });
        await page.goto('https://www.wholesaledepot.co.kr/wms/goods/goods_list2.php', { waitUntil: 'networkidle0' });
        await page.select('#search_key', 'goodscd2');
        await page.select('#list_size', '100');
        await page.click('#s_open1');
        await page.click('#s_runout1');
        await page.type('#search_str', searchStr);
        await page.click('#sfrm > div > div > div > ul:nth-child(16) > li:nth-child(2) > button');
        await new Promise((page) => setTimeout(page, 3000));
        let status = await page.evaluate(() => {
            const checkbox = document.querySelector('#chkIdxAll');
            if (checkbox) {
                checkbox.click();
                return true;
            } else {
                return false;
            }
        });
        if (status === false) {
            console.log(status);
            return;
        }
        await new Promise((page) => setTimeout(page, 1000));
        page.on('dialog', async dialog => {
            const message = dialog.message();
            if (message.includes('완료')) {
                console.log(true);
            }
            await dialog.accept();
            return;
        });
        await page.click('body > div.scm_contents_warp > div.container-fluid > div.oh-tbwarp > table > tbody > tr:nth-child(1) > td > button.btn.btn-danger.btn-xs');
        await new Promise((page) => setTimeout(page, 5000));
        console.log(status);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
