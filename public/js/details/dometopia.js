const puppeteer = require('puppeteer');

const loginToSite = async (page, username, password) => {
    await page.goto('https://dometopia.com/member/login', { waitUntil: 'networkidle2', timeout: 0 });
    await page.type('#userid', username);
    await page.type('#password', password);
    await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
    await page.waitForNavigation();
};

const getProductDetails = async (page, productHref) => {
    await page.goto(productHref, { waitUntil: 'load', timeout: 0 });
    return page.evaluate((productHref) => {
        const stockSelector = '#select_option_lay > div.quantity_box > table > tbody > tr:nth-child(2) > td';
        const stockElement = document.querySelector(stockSelector).textContent.trim();
        const stock = parseInt(stockElement.replace(/[^\d]/g, ''));
        if (stock < 50) {
            return false;
        }
        const baseURL = 'https://dometopia.com';
        const productName = document.querySelector('#info > div.goods_info.clearbox > form > div.container > div > h2').textContent.trim();
        let productPrice = document.querySelector('#info > div.goods_info.clearbox > form > div.container > table > tbody > tr:nth-child(2) > td > ul:nth-child(2) > li:nth-child(3) > span').textContent.trim();
        productPrice = parseInt(productPrice.replace(/[^\d]/g, ''));

        const productImages = document.querySelectorAll('#goods_thumbs > div.box > div.slides_container.hide img');
        const productImage = productImages.length > 3 ? productImages[2].src : productImages[0]?.src;

        const images = document.querySelectorAll('#detail > div > div.section.info > div.goods_description > div.detail-img img');
        const productDetail = images.length === 0 ? [] : Array.from(images, img => {
            let src = img.getAttribute('src');
            return src.startsWith('http://') || src.startsWith('https://') ? src : new URL(src, baseURL).href;
        });

        return {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption: false,
            productOptions: [],
            productHref,
            sellerID: 3
        };
    }, productHref);
};

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [productHref, username, password] = process.argv.slice(2);
        await loginToSite(page, username, password);
        const productContents = await getProductDetails(page, productHref);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
