const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.goto('https://www.wholesaledepot.co.kr/wms/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('#id', username);
        await page.type('#passwd', password);
        await page.click('#frm > div > div > button');
        await page.waitForNavigation();
        await page.goto('https://www.wholesaledepot.co.kr/wms/goods/goods_list2.php', { waitUntil: 'networkidle2' });
        await page.select('#search_key', 'goodscd2');
        await page.type('#search_str', productCode);
        await page.click('#s_open1');
        await page.click('#s_runout1');
        await page.click('#sfrm > div > div > div > ul:nth-child(16) > li:nth-child(2) > button');
        const checkbox = await page.waitForSelector('#chkIdx0');
        await checkbox.click();
        page.on('dialog', async dialog => {
            console.log(true);
            await dialog.accept();
            return;
        });
        await page.click('body > div.scm_contents_warp > div.container-fluid > div.oh-tbwarp > table > tbody > tr:nth-child(1) > td > button:nth-child(5)');
        await new Promise((page) => setTimeout(page, 3000));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
