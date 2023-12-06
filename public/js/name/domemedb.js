const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [productHref] = args;
        // 웹 페이지로 이동
        await page.goto(productHref);
        await page.waitForSelector('#lInfoItemTitle');
        let element = await page.$('#lInfoItemTitle')
        const productName = await page.evaluate(el => el.textContent, element);
        // 상품 정보 출력
        console.log(JSON.stringify(productName.trimStart()));
    } catch (error) {
        console.error('오류 발생:', error);
    } finally {
        await browser.close();
    }
})();
