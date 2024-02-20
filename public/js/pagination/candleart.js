const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL] = args;
        await page.goto(listURL, { waitUntil: 'networkidle2', timeout: 0 });
        const numProducts = await page.evaluate(() => {//url변경하였고 정규식 그대로 사용 숫자만 잘 추출함
            const numProductsText = document.querySelector('#Product_ListMenu > p').textContent.trim();
            const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
            return numProducts;
        });
        console.log(numProducts);
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
