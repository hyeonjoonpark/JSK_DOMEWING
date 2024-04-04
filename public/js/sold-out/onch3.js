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
        const searchStr = productCodes.join(',');

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

const login = async (page, username, password) => {
    await page.goto('https://www.onch3.co.kr/login/login_web.php', { waitUntil: 'networkidle0' });
    await page.type('body > div > form > input:nth-child(3)', username);
    await page.type('body > div > form > input:nth-child(5)', password);
    await page.click('body > div > form > button');//로그인
    await page.waitForNavigation({ waitUntil: 'load' });
};

async function processPageList(page, searchStr) {
    await page.goto('https://www.onch3.co.kr/admin_mem_made_list.php?ost=&svc=', { waitUntil: 'networkidle0' });
    await page.click('#searchJejoCode'); //상품코드
    await delay(1000);
    await page.type('#searchForm > div > div:nth-child(5) > div > textarea', searchStr); //입력창에 입력
    await delay(1000);
    await page.click('#searchForm > div > div.filter_btn_wrap > div.fbw_top > span.fb_search_btn');//검색버튼 클릭
    await delay(1000);
}

async function doSoldOut(page) {
    const productElement = await page.$$('body > center > table > tbody > tr:nth-child(3) > td:nth-child(3) > div.my_pg_con_box > div.my_pg_con > div > ul > li');
    if (productElement.length < 1) {
        console.log(false);
        return;
    }
    await delay(1000);
    await page.evaluate(() => {
        const inputElement = document.querySelector('body > center > table > tbody > tr:nth-child(3) > td:nth-child(3) > div.my_pg_con_box > div.my_pg_con > div > div.made_list_title > div.made_select > input');
        inputElement?.click();
    });
    const soldOutMsg = "안녕하세요 대표님\n해당상품 재고소진으로 품절처리 드립니다.\n재입고 일정이 현재 따로 잡혀있지 않습니다.\n감사합니다.";

    await page.click('#searchForm > div > div.filter_btn_wrap > div.fbw_bot > span');
    await delay(2000); await page.select('#searchForm > div > div.filter_btn_wrap > div.fbw_bot > div > div.ss_type > select', "5");
    await delay(2000); await page.type('#searchForm > div > div.filter_btn_wrap > div.fbw_bot > div > div.ss_content > textarea', soldOutMsg)
    await delay(2000); await page.click('#searchForm > div > div.filter_btn_wrap > div.fbw_bot > div > div.ss_btn_group > a:nth-child(1)');


    await delay(2000);
}


async function clearPopup(page) {
    page.on('dialog', async dialog => {
        await dialog.accept();
        return console.log(true);
    });
}
