const puppeteer = require('puppeteer');
const { signIn, checkImageUrl, checkProductName, formatProductName } = require('./common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);

    await page.setDefaultNavigationTimeout(0);

    try {
        await signIn(page, username, password, 'https://soggupnoli.com/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.member_login_box > div.login_input_sec > button');
        await moveToPage(page, listURL);
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
async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const url = await page.evaluate((listURL) => {
        const numTotal = parseInt(document.querySelector("#contents > div > div > div.goods_list_item > div.goods_pick_list > span > strong").textContent.trim().replace(/[^\d]/g, ''));
        listURL += '&sort=&pageNum=' + numTotal;
        return listURL;
    }, listURL);
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}
async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('div.item_cont');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const isSoldOut = Array.from(pe.querySelectorAll('div > div.item_info_cont > div.item_icon_box > img')).some(img => img.src === 'https://cdn-pro-web-250-118.cdn-nhncommerce.com/sogguptr0066_godomall_com/data/icon/goods_icon/icon_soldout.gif');
            if (isSoldOut) {
                return false;
            }

            const nameElement = pe.querySelector('strong.item_name');
            if (!nameElement) {
                return false;
            }

            const priceElements = pe.querySelectorAll('strong.item_price > span');
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }

            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }

            const imageElement = pe.querySelector('div > div.item_photo_box > a > img')
            if (!imageElement) {
                return false;
            }
            const hrefElement = pe.querySelector('div > div.item_photo_box > a');
            if (!hrefElement) {
                return false;
            }
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            // const href = productElement.querySelector('div:nth-child(1) > a').href;
            const href = 'https://www.soggupnoli.com/' + hrefElement.getAttribute('href');
            const platform = '소꿉노리';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
