const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        await signIn(page, username, password);
        await page.goto(productHref, { waitUntil: 'networkidle0', timeout: 0 });
        const product = await scrapeProduct(page, productHref);
        console.log(JSON.stringify(product));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('http://www.autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0', timeout: 0 });
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    await page.waitForSelector('#wrap');
}
async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {
        let productName = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.top_title_bar > h3').textContent.trim();
        const productStandard = document.querySelector('#goods_spec > form > div:nth-child(4) > b').textContent.trim();
        const productPrice = document.querySelector('#price').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#objImg').src;
        const images = document.querySelectorAll('#contents > table > tbody > tr > td > p > img');
        // 각 이미지의 src 속성을 절대 경로로 변환합니다.
        const productDetail = Array.from(images, img => {
            let src = img.getAttribute('src');
            return src;
        });
        const hasOption = true;
        const productOptions = [];
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
    }, productHref);
    return product;
}
