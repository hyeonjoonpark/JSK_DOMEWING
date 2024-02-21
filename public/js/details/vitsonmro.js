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
    await page.goto('https://vitsonmro.com/mro/login.do', { waitUntil: 'networkidle0', timeout: 0 });
    await page.type('#custId', username);
    await page.type('#custPw', password);
    await page.click('#loginForm > div > a:nth-child(3)');
    await page.waitForNavigation();
}
async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {
        let productName = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.top_title_bar > h3').textContent.trim();
        const productStandard = document.querySelector('#table > tbody > tr:nth-child(2) > td:nth-child(2)').textContent.trim();
        productName += ' ' + productStandard;
        const productPrice = document.querySelector('#negoPrice').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.deal_view > div.deal_gallery > div.swiper-container.gallery-top.swiper-container-horizontal > div > div.swiper-slide.swiper-slide-active > img').src;
        const images = document.querySelectorAll('#detail_box > div > ul img');
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
            sellerID: 13
        };
    }, productHref);
    return product;
}