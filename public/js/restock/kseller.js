const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, productCode] = args;
        page.on('dialog', async dialog => {
            await dialog.accept();
            return;
        });
        await login(page, username, password);

        await page.goto('http://www.kseller.kr/kpm/shop_item/item_list.php', { waitUntil: 'networkidle0' });



        await page.click('#content > table.tb11 > tbody > tr:nth-child(3) > td:nth-child(2) > input[type=radio]:nth-child(3)');//품절상품만보기
        await page.select('#q_type', 'vender_code'); //상품코드
        await delay(1000);
        await page.select('#content > table.tb11 > tbody > tr:nth-child(8) > td.cttd > select:nth-child(2)', '500');//한번에 보는 갯수
        await page.type('#q2', productCode); //입력창에 입력
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#content > table.tb11 > tbody > tr:nth-child(8) > td.cttd > input.bt_blue');//검색버튼 클릭
        await new Promise((page) => setTimeout(page, 1000));

        const productElement = await page.$$('#content > table.tb12 > tbody > tr');//제품개수가 없는지 확인
        if (productElement.length < 2) {
            console.log(false);
            return;
        }

        await new Promise((page) => setTimeout(page, 1000));

        await page.evaluate(() => {
            const inputElement = document.querySelector('#content > table.tb12 > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=checkbox]');//체크박스
            inputElement?.click();
        });
        await new Promise((page) => setTimeout(page, 1000));
        await page.click('#btn_total_sale');
        console.log(true);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

const login = async (page, username, password) => {
    await page.goto('http://www.kseller.kr/index.php?vhtml=mb/login_form', { waitUntil: 'networkidle0' });
    await page.click('#t2');//공급사모드로 변경
    await page.type('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(1) > td > input', username);
    await page.type('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(3) > td > input', password);
    await page.click('#body_center_wrap > div > div:nth-child(3) > table > tbody > tr:nth-child(5) > td > input');//로그인
    await page.waitForNavigation({ waitUntil: 'load' });
};
