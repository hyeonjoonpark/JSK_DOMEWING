const puppeteer = require('puppeteer');
const getForbiddenWords = require('../forbidden_words');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password);
        await processPage(page, listURL);
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
    await page.goto('https://www.vipb2b.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.evaluate(() => {
        document.querySelector('#formLogin').submit();
    });
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div > div > div.goods_list_item > div.goods_pick_list > span > strong').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    listURL += '&sort=&pageNum=' + numProducts;
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('div.item_basket_type > ul li');

        const products = [];

        for (const productElement of productElements) {
            const promotionElements = productElement.querySelectorAll('div > div.item_info_cont > div.item_icon_box img');
            const soldOutSrc = "https://cdn-pro-web-228-238.cdn-nhncommerce.com/vipb2btr9691_godomall_com/data/icon/goods_icon/icon_soldout.gif";
            let sibal = true;
            for (const promotionElement of promotionElements) {
                if (promotionElement.getAttribute('src') === soldOutSrc) {
                    sibal = false; // 품절된 상품은 건너뜁니다.
                }
            }
            if (sibal === false) {
                continue;
            }
            const nameElement = productElement.querySelector('div > div.item_info_cont > div.item_tit_box > a > strong');
            if (!nameElement) continue; // 상품명이 없는 경우 건너뜁니다.

            let productName = nameElement.textContent.trim() || nameElement.innerText.trim();
            // '준수'나 '참고'가 포함된 경우 건너뜁니다.
            if (productName.includes('준수') || productName.includes('참고')) {
                continue;
            }

            // 상품명에서 불필요한 문자열 제거
            const name = productName.replace(/\(.*?\)/g, '').replace(/\[.*?\]/g, '');
            for (const forbiddenWord of forbiddenWords) {
                if (name.includes(forbiddenWord)) {
                    continue;
                }
            }

            const imageElement = productElement.querySelector('div > div.item_photo_box > a > img');
            const image = imageElement.src;
            const priceElement = productElement.querySelector('div > div.item_info_cont > div.item_money_box > strong > span');
            if (!priceElement) continue; // 가격 정보가 없는 경우 건너뜁니다.
            const price = priceElement.textContent.trim().replace(/[^\d]/g, '');
            if (price < 1) {
                return false;
            }

            const hrefElement = productElement.querySelector('div > div.item_photo_box > a');
            if (!hrefElement) {
                return false;
            }
            const href = hrefElement.href.trim();

            const product = {
                name,
                image,
                price,
                href,
                platform: "브이아이피"
            };

            products.push(product);
        }

        return products;
    }, forbiddenWords);

    return products;
}
