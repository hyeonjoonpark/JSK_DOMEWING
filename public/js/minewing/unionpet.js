const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
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
    await page.goto('https://www.unionpet.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#rightQuick > ul.right_btn_wrap > li:nth-child(7) > p > img');
    await new Promise((page) => setTimeout(page, 1000));
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
    const newUrl = listUrlSplit[0] + '?page=' + curPage + '&' + listUrlSplit[1] + '&sort=&pageNum=40';
    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div > div > div.goods_list_item > div.goods_list > div > div.item_gallery_type > ul > li');
        for (const productElement of productElements) {
            if (checkSoldOutProduct(productElement)) {
                continue;
            }
            const nameElement = productElement.querySelector('div > div.item_info_cont > div.item_tit_box > a > strong').textContent.trim();
            if (checkSkipProduct(nameElement)) {
                continue;
            }
            const imageElement = productElement.querySelector('div > div.item_photo_box > a > img');
            const priceElement = productElement.querySelector('div > div.item_info_cont > div.item_money_box > strong > span');
            if (!priceElement) {
                continue;
            }
            const hrefElement = productElement.querySelector('div > div.item_photo_box > a').href.trim();


            const name = nameElement ? removeBrandNameInProductName(nameElement) : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? makeSafetyUrl(hrefElement) : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "유니온펫";
            products.push({ name, price, image, href, platform });
        }
        return products;

        function makeSafetyUrl(href) {
            let safetyUrl = '';
            if (href.startsWith('../')) {
                safetyUrl = href.slice(2);
            }
            else safetyUrl = href;
            return safetyUrl;
        }
        function checkSoldOutProduct(productElement) {
            const soldOutSrc = "https://cdn-pro-web-212-222.cdn-nhncommerce.com/unionptr8532_godomall_com/data/icon/goods_icon/icon_soldout.gif";
            const soldOutImages = productElement.querySelectorAll('div > div.item_info_cont > div.item_icon_box > img');
            for (const soldOutImage of soldOutImages) {
                soldOut = soldOutImage.src;
                if (soldOut === soldOutSrc) {
                    return true;
                }
            }
            return false;
        }
        function checkSkipProduct(productName) {
            const imminentDiscount = "임박할인";
            const outOfStock = "소진";
            const nonReturnable = "반품불가";
            const directShipping = "직배송";
            if (productName.includes(imminentDiscount) || productName.includes(outOfStock) || productName.includes(nonReturnable) || productName.includes(directShipping)) {
                return true;
            }
            return false;
        }
        function removeBrandNameInProductName(nameElement) {
            const name = nameElement.replace(/\[.*?\]/g, "");
            return name.trim();
        }
    });
    return products;
}





