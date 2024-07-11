const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName } = require('./common.js');
(async () => {

    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ 'width': 1500, 'height': 1000 });
    const [listUrl, username, password] = process.argv.slice(2);

    await page.setDefaultNavigationTimeout(0);

    try {
        await signIn(page, username, password, 'https://goodsdeco.com/member/login.php', '#loginId', '#loginPwd', 'div.login_input_sec > button');
        await goToAttempts(page, listUrl, 'domcontentloaded');

        const totalProducts = await getNumPage(page);
        await goToAttempts(page, listUrl + '&pageNum=' + totalProducts, 'domcontentloaded');

        const products = [];
        const listProducts = await getListProducts(page);
        for (const product of listProducts) {
            const isValidImage = await checkImageUrl(product.image);
            const isValidProduct = await checkProductName(product.name);
            if (isValidImage && isValidProduct) {
                product.name = await formatProductName(product.name);
                products.push(product);
            }
        }

        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function getNumPage(page) {
    return await page.evaluate(() => {
        const numProductsText = document.querySelector('div.goods_pick_list > span > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
}
async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('div.item_basket_type > ul > li');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const isSoldOut = Array.from(pe.querySelectorAll('div.item_icon_box > img')).some(img => img.src === 'https://cdn-pro-web-250-83.cdn-nhncommerce.com/gdeco8066_godomall_com/data/icon/goods_icon/icon_soldout.gif');
            if (isSoldOut) {
                return false;
            }
            const nameElement = pe.querySelector('div.item_tit_box > a > strong');
            if (!nameElement) {
                return false;
            }
            const priceElements = pe.querySelectorAll('div.content > div.goods_list_item > div.goods_list div.item_info_cont > div.item_money_box > strong')
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }
            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }
            const imageElement = pe.querySelector('li > div > div.item_photo_box > a > img')
            if (!imageElement) {
                return false;
            }
            const hrefElement = pe.querySelector('li > div > div.item_photo_box > a');
            if (!hrefElement) {
                return false;
            }
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            let href = 'https://goodsdeco.com/' + hrefElement.getAttribute('href');
            const platform = '굿즈데코';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
