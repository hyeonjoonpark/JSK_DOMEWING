const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        // const [tempFilePath, username, password] = args;
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const username = 'sungiltradekorea';
        const password = 'tjddlf88!@#';
        const urls = ['http://autocarfeel.co.kr/shop/goods/goods_view.php?goodsno=6401&category=018'];
        await login(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page);
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
// 사용자 로그인을 처리하는 비동기 함수입니다.
async function login(page, username, password) {
    // 로그인 페이지로 이동합니다.
    await page.goto('http://autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    // 사용자 이름과 비밀번호 입력 필드에 값을 입력합니다.
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    // 로그인 버튼을 클릭합니다.
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    // 페이지 내비게이션이 완료될 때까지 기다립니다.
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}
async function scrapeProduct(page) {
    const product = await page.evaluate(() => {
        // productName
        const productNameElement = document.querySelector('#goods_spec > form > div:nth-child(4) > b');
        productNameElement.querySelectorAll('font[color="red"]').forEach(el => el.remove());
        const productName = productNameElement.textContent.trim();
        // productPrice
        const productPriceText = document.querySelector('#price').textContent.trim();
        const productPrice = parseInt(productPriceText.replace(/[^\d]/g, ''));
        // productImage
        const baseUrl = 'http://autocarfeel.co.kr/shop/data';
        const imageSrc = productElement.querySelector('div:nth-child(1) > a > img').getAttribute('src');
        const toAbsoluteUrl = (src, baseUrl) => new URL(src.replace(/^\.\.\//, ''), baseUrl).href;
        const productImage = toAbsoluteUrl(imageSrc, baseUrl);
        // hasOption
        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 20
        };
    });
    return product;
}