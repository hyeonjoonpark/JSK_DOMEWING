const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, listURL, i);
            let list = await scrapeProducts(page);
            products.push(...list);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();


async function signIn(page, username, password) {
    await page.goto('https://www.sellerfriend.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.member_login_box > div.login_input_sec > button');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div > div > div.goods_list_item > div.goods_pick_list > span > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 40;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    const listUrlSplit = listUrl.split('?');
    let newUrl = listUrlSplit[0] + '?page=' + curPage + '&' + listUrlSplit[1] + '&sort=&pageNum=40';
    if (listUrl.includes('?page=')) {
        const pageSplit = listUrl.split('&');
        const categoryUrl = pageSplit[1];
        newUrl = listUrlSplit[0] + '?page=' + curPage + '&' + categoryUrl + '&sort=&pageNum=40';
    }
    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div > div > div.goods_list_item > div.goods_list > div > div > ul li');
        for (const productElement of productElements) {

            const promotionElement = productElement.querySelector('div > div.item_photo_box > a > strong');
            if (promotionElement) {

                if (checkSkipProduct(promotionElement)) {
                    continue;
                }

            }

            const nameElement = productElement.querySelector('div > div.item_info_cont > div.item_tit_box > a > strong');
            const imageElement = productElement.querySelector('div > div.item_photo_box > a > img');
            const priceElement = productElement.querySelector('div > div.item_info_cont > div.item_money_box > strong > span');
            const hrefElement = productElement.querySelector('div > div.item_photo_box > a');



            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price no found';
            const platform = "셀러프랜드";
            products.push({ name, price, image, href, platform });
        }
        return products;

        function checkSkipProduct(promotionElement) {
            const soldOut = "SOLD OUT";
            const promotionSrc = promotionElement.textContent.trim();
            if (promotionSrc.includes(soldOut)) {
                return true;
            }
            return false;
        }
    });
    return products;
}





