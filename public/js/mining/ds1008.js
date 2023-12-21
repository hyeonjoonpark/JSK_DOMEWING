const puppeteer = require('puppeteer');
// 상품 정보를 추출하는 비동기 함수
async function extractProductsOnPage(page) {
    return await page.evaluate(() => {
        const productElements = document.querySelectorAll('#content > div > div > div.cg-main > div.goods-list > div.item-display.type-cart > div > ul > li');
        const productsArr = [];
        for (const productElement of productElements) {
            const priceText = productElement.querySelector('div > div.price.gd-default > span > strong').textContent;
            const koreanRegex = /[\u3131-\uD79D]/ugi;
            if (koreanRegex.test(priceText)) {
                continue;
            }
            const priceNumber = priceText.match(/\d+/g);
            const price = parseInt(priceNumber.join(''), 10);
            const name = productElement.querySelector('div > div.txt > a > strong').textContent;
            const imageText = productElement.querySelector('div > div.thumbnail > a > img');
            const image = imageText.getAttribute('src');
            const hrefSelector = productElement.querySelector('div > div.thumbnail > a').getAttribute('href');
            const href = 'https://www.ds1008.com/goods/goods_view.php?goodsNo=' + hrefSelector.match(/\d+/g).join('');
            const platform = '씨오코리아';
            productsArr.push({ name, price, image, href, platform });
        }
        return productsArr;
    });
}
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    // page.setDefaultNavigationTimeout(0);
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password, curPage] = args;
        const pageURL = '&page=' + curPage;
        const fullURL = listURL + pageURL;
        await page.goto('https://www.ds1008.com/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('#loginId', username);
        await page.type('#loginPwd', password);
        await page.click('#formLogin > div.login > button');
        await page.waitForNavigation();
        // 웹 페이지로 이동
        await page.goto(fullURL, { waitUntil: 'networkidle2' });
        const allProducts = await extractProductsOnPage(page);
        // 상품 정보 출력
        console.log(JSON.stringify(allProducts));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
