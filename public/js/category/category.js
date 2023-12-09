const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const keyword = process.argv[2];
        // 웹 페이지로 이동
        await page.goto('https://domesin.com/scm/login.html');
        await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(1) > td > input');
        await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(3) > td > input');
        await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(3) > input[type=image]');
        await page.type(
            'body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(1) > td > input',
            'sungiltradekorea'
        );
        await page.type(
            'body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(3) > td > input',
            'tjddlf88!@'
        );
        await page.click(
            'body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(3) > input[type=image]'
        );
        await page.waitForNavigation();
        await page.goto("http://domesin.com/scm/M_item/item_form.html");
        async function wait(seconds) {
            return new Promise(resolve => setTimeout(resolve, seconds * 1000));
        }
        await page.waitForSelector('input[name="cat_q"]');
        await page.waitForSelector('select[id="cat_search_select"]');
        await page.type('input[name="cat_q"]', keyword);
        await page.click('input[id="cat_search_bt"]');
        await wait(1);
        // 5초 동안 기다린 후 코드를 진행
        // 원하는 select 요소의 CSS 셀렉터
        const selectSelector = 'select[id="cat_search_select"]';
        const selectElement = await page.$(selectSelector);

        // select 요소 내의 모든 option 요소 선택
        const optionSelectors = 'option';
        const optionElements = await selectElement.$$(optionSelectors);

        // option 요소들의 값을 추출하여 배열에 저장
        const optionValues = [];
        for (const optionElement of optionElements) {
            const optionValue = await optionElement.evaluate(el => el.innerText);
            optionValues.push({ optionValue });
        }

        // 추출한 option 값들 출력
        console.log(JSON.stringify(optionValues));
    } catch (error) {
        console.error('오류 발생:', error);
    } finally {
        await browser.close();
    }
})();
