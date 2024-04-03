const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password);
        await processPage(page, listURL);
        const products = await scrapeProducts(page);
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
    const loginButton = await page.$('#formLogin > div.member_login_box > div.login_input_sec > button');
    await loginButton.evaluate(b => b.click());
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div > div > div.goods_list_item > div.goods_pick_list > span > strong').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    listURL += '&sort=&pageNum=' + numProducts;
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('div.item_basket_type > ul li');

        const products = [];

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('div > div.item_info_cont > div.item_icon_box > img');
            const soldOutSrc = "https://cdn-pro-web-228-238.cdn-nhncommerce.com/vipb2btr9691_godomall_com/data/icon/goods_icon/icon_soldout.gif";
            if (promotionElement && promotionElement.getAttribute('src') === soldOutSrc) {
                continue; // 품절된 상품은 건너뜁니다.
            }

            const nameElement = productElement.querySelector('div > div.item_info_cont > div.item_tit_box > a > strong');
            if (!nameElement) continue; // 상품명이 없는 경우 건너뜁니다.

            let productName = nameElement.textContent.trim() || nameElement.innerText.trim();
            // '준수'나 '참고'가 포함된 경우 건너뜁니다.
            if (productName.includes('준수') || productName.includes('참고')) {
                continue;
            }

            // 상품명에서 불필요한 문자열 제거
            productName = productName.replace(/\(.*?\)/g, '').replace(/\[.*?\]/g, '');

            const imageElement = productElement.querySelector('div > div.item_photo_box > a > img');
            const priceElement = productElement.querySelector('div > div.item_info_cont > div.item_money_box > strong > span');
            if (!priceElement) continue; // 가격 정보가 없는 경우 건너뜁니다.

            const hrefElement = productElement.querySelector('div > div.item_photo_box > a');

            const product = {
                name: productName,
                image: imageElement ? imageElement.src.trim() : 'Image URL not found',
                price: priceElement.textContent.trim().replace(/[^\d]/g, ''),
                href: hrefElement ? hrefElement.href.trim() : 'Detail page URL not found',
                platform: "브이아이피"
            };

            products.push(product);
        }

        return products;
    });

    return products;
}
