
const puppeteer = require('puppeteer');
const { scrollDown, goToAttempts, signIn } = require('./common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: false, args: ['--start-maximized'] });
    const page = await browser.newPage();
    await page.setViewport({ 'width': 1900, 'height': 1080 });
    const [listUrl, username, password] = process.argv.slice(2);
    await page.setDefaultNavigationTimeout(0);
    const products = [];
    let exitType = 0;
    let errMsg = 'Error occurred';
    try {
        await signIn(page, username, password, 'https://goodsdeco.com/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.member_login_box > div.login_input_sec > button');
        await goToAttempts(page, listUrl, 'domcontentloaded');
        if (listUrl.match(/page=(\d+)/)) {
            await goToAttempts(page, listUrl, 'domcontentloaded');
            const listProducts = await getListProducts(page);
            products.push(...listProducts);
        } else {

            const lastPageNumber = await getLastPageNumber(page);
            for (let i = lastPageNumber; i > 0; i--) {
                await goToAttempts(page, listUrl + '&page=' + i, 'domcontentloaded');
                const listProducts = await getListProducts(page);
                products.push(...listProducts);
            }
        }
    } catch (error) {
        exitType = 1;
        errMsg = error;
    } finally {
        if (exitType === 0) {
            console.log(JSON.stringify(products));
        } else if (exitType === 1) {
            if (products.length > 0) {
                console.log(JSON.stringify(products));
            } else {
                console.error(errMsg);
            }
        }
        process.exit(exitType)
    }
})();
async function getLastPageNumber(page) {

    const lastPageNumber = await page.evaluate(() => {
        const lastPageUrl = document.querySelector('li.btn_page.btn_page_last > a').getAttribute('href');

        let match = lastPageUrl.match(/page=(\d+)/);
        if (match[1] !== undefined) {
            pageValue = match[1];
        }
        return pageValue;
    });
    return lastPageNumber ? parseInt(lastPageNumber) : 1;
}
async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('div.goods_list_item > div.goods_list > div > div.item_basket_type > ul > li');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const nameElement = pe.querySelector('li > div > div.item_info_cont > div.item_tit_box > a > strong');
            if (!nameElement) {
                return false;
            }
            const priceElements = pe.querySelectorAll('#contents > div > div.content > div.goods_list_item > div.goods_list > div > div.item_basket_type > ul > li > div > div.item_info_cont > div.item_money_box > del')
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
            let href = 'https://goodsdeco.com' + hrefElement.getAttribute('href');
            href = href.replace('.com..', '.com');
            const platform = '굿즈데코';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
