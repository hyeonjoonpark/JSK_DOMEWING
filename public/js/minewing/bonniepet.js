const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
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
    await page.goto('http://bonniepet.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}
async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
    // 상품 개수를 페이지에서 추출합니다.
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#content > div.contents > div > div.cg-main > div > span > strong').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    // 상품 개수를 기반으로 최종 URL을 구성합니다.
    listURL += '&sort=&pageNum=' + numProducts;
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#content > div.contents > div > div.cg-main > div > div > div > ul li');

        function checkSkipProduct(productElement) {
            const soldOutImageSrc = "https://cdn-pro-web-220-151.cdn-nhncommerce.com/moneyball11_godomall_com/data/icon/goods_icon/icon_soldout.gif";
            const promotionElement = productElement.querySelector('#content div.contents div.cg-main div ul li div.txt div img');
            if (promotionElement && promotionElement.getAttribute('src') === soldOutImageSrc) {
                return true;
            }
            return false;
        }

        for (const productElement of productElements) {
            if (checkSkipProduct(productElement)) {
                continue; // 이 제품은 매진되어 건너뜁니다.
            }

            // 제품 정보를 추출하는 코드를 for 루프 내부로 옮겼습니다.
            const nameElement = productElement.querySelector('#content > div.contents > div > div.cg-main > div > div > div > ul li div.txt > a > strong');
            const imageElement = productElement.querySelector('#content > div.contents > div > div.cg-main > div > div > div > ul li div.thumbnail > a > img');
            const priceElement = productElement.querySelector('#content > div.contents > div > div.cg-main > div > div > div > ul li div.price.gd-default > span > strong');
            const hrefElement = productElement.querySelector('#content > div.contents > div > div.cg-main > div > div > div > ul li div.thumbnail > a');

            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "바니펫";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}

