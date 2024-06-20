const getForbiddenWords = require('../forbidden_words');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password);
        await moveToPage(page, listURL);
        const forbiddenWords = getForbiddenWords();
        const products = await scrapeProducts(page, forbiddenWords);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://www.modoosale.co.kr/member/login.php?returnUrl=%2Fgoods%2Fgoods_view.php%3FgoodsNo%3D1000107036', { waitUntil: 'networkidle0' });
    await page.type('input[name="loginId"]', username);
    await page.type('input[name="loginPwd"]', password);
    await page.click('#formLogin > div.member_login_box > div.login_input_sec > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const url = await page.evaluate((listURL) => {
        const numTotal = parseInt(document.querySelector("#contents > div.sub_content > div > div.goods_list_item > div.goods_pick_list > span > strong").textContent.trim().replace(/[^\d]/g, ''));
        listURL += '&sort=&pageNum=' + numTotal;
        return listURL;
    }, listURL);
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('#contents > div.sub_content > div > div.goods_list_item > div.goods_list > div > div.item_basket_type > ul li');
        const products = [];
        for (const productElement of productElements) {
            const product = scrapeProduct(productElement, forbiddenWords);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        return products;
        function scrapeProduct(productElement, forbiddenWords) {
            try { //품절 확인
                const soldOutImageElement = productElement.querySelector('img[src="https://cdn-pro-web-222-171.cdn-nhncommerce.com/mys05290_godomall_com/data/icon/goods_icon/icon_soldout.gif"]');
                if (soldOutImageElement) {
                    return false;
                }
                const name = productElement.querySelector('div.item_info_cont > div.item_tit_box > a > strong').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const price = parseInt(productElement.querySelector('div.item_info_cont > div.item_money_box > strong > span').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('ul > li > div > div.item_photo_box > a > img').src;
                const href = productElement.querySelector('ul > li > div > div.item_photo_box > a').href;
                const platform = "모두세일";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
