const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const args = process.argv.slice(2);
    try {
        const [listURL, username, password] = args;
        await login(page, username, password);
        await processPage(page, listURL);
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        browser.close();
    }
});
async function login(page, username, password) {
    await page.goto('http://www.autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0', timeout: 0 });
    await page.type('#userid', username);
    await page.type('#password', password);
    await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
    await page.waitForNavigation();
}
async function processPage(page, listURL) {
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#b_white > font > b').textContent;
        const numProducts = parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
        return numProducts;
    });
    listURL += '&page_num=' + numProducts;
    await page.goto(listURL, { waitUntil: 'networkidle0', timeout: 0 });
}
// 이 메소드는 BitsOnMRO를 사용하여 상품 정보를 스크래핑합니다.
// 모든 product 객체의 필드명은 일치해야 합니다. 오토카필을 사용해 셀렉터를 정확히 지정하고 필요에 따라 수정하세요.
// 문자열 처리를 위한 JavaScript 함수를 반드시 숙지하세요.
// 또한, 오토카필 문자열을 적절히 편집하여 각 필드의 데이터 타입에 맞게 조정하세요.
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        function processProduct(productElement) {
            const stockText = productElement.querySelector('td:nth-child(9) > span.hdsp_bot').textContent.trim();
            if (stockText !== '재고보유') {
                return false;
            }
            const productName = productElement.querySelector('td:nth-child(6) > span.hdsp_top.link > a').textContent.trim();
            const standard = productElement.querySelector('td:nth-child(6) > span.hdsp_bot').textContent.trim();
            const name = productName + ' ' + standard;
            const productPriceText = productElement.querySelector('td:nth-child(10) > span.hdsp_top.price_cr').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const image = productElement.querySelector('td:nth-child(4) > div > img').getAttribute('src');
            const productCode = productElement.querySelector('td:nth-child(5) > span.hdsp_top').textContent.replace(/[^0-9]/g, '').trim();
            const href = 'https://vitsonmro.com/mro/shop/productDetail.do?productCode=' + productCode;
            const platform = '비츠온엠알오';
            return { name, price, image, href, platform };
        }
        const productElements = document.querySelectorAll('#grid > div.k-grid-content.k-auto-scrollable > table > tbody tr');
        const products = [];
        for (const productElement of productElements) {
            const result = processProduct(productElement);
            if (result !== false) {
                products.push(result);
            }
        }
        return products;
    });
    return products;
}