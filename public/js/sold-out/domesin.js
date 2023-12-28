const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({
        headless: false,
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        ignoreDefaultArgs: ['--enable-automation']
    });
    const pages = await browser.pages();
    const page = pages[0];
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        await page.goto('https://www.domesin.com/scm/login.html', { waitUntil: 'networkidle2' });
        const usernameInput = await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(1) > td > input');
        const passwordInput = await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(3) > td > input');
        const signInBtn = await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(3) > input[type=image]');
        await usernameInput.type(username);
        await passwordInput.type(password);
        await new Promise((page) => setTimeout(page, 3000));
        await signInBtn.click(username);
        await page.waitForNavigation({ timeout: 0 });
        await page.goto('https://www.domesin.com/scm/M_item/item_list.html?cate1=&cate2=&cate3=&cate4=&cid=&date=w&start_date=&end_date=&status=&raid=&i_type=&adult=&delivery_type=&isreturn=&tax=&item_sale_type=&ok=&is_overseas=&ls=&q_type=vender_code&rows=20&isort=iid&q=&q2=' + productCode, { waitUntil: 'networkidle2' });
        await new Promise((page) => setTimeout(page, 3000));
        await page.click('#main > table.tb12 > tbody > tr:nth-child(2) > td:nth-child(1) > div:nth-child(2) > input');
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        const [newPage] = await Promise.all([
            new Promise(resolve => browser.once('targetcreated', target => resolve(target.page()))),
            page.click('#btn_total_sold')
        ]);

        // 새 페이지에서 원하는 요소의 textContent 가져오기
        if (newPage) {
            await newPage.waitForSelector('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)');
            const textContent = await newPage.$eval('body > table > tbody > tr:nth-child(2) > td > div:nth-child(2) > table > tbody > tr:nth-child(2) > td:nth-child(4)', element => element.textContent);
            if (textContent.includes('변경')) {
                console.log(true);
            }
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
