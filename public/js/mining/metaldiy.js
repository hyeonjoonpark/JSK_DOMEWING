const puppeteer = require('puppeteer');
// 상품 정보를 추출하는 비동기 함수
async function extractProductsOnPage(page) {
    return await page.evaluate(() => {
        const productElements = document.querySelectorAll('ul[class="goodsBox"] > li');
        const productsArr = [];
        for (const productElement of productElements) {
            const nameText = productElement.querySelector('ul > li.goods_md > a').textContent;
            const name = nameText;
            if (name.includes('품절')) {
                continue;
            }
            const priceText = productElement.querySelector('ul > li.goods_bm > span.price > strong').textContent;
            const priceNumber = priceText.match(/\d+/g);
            const price = parseInt(priceNumber.join(''), 10);
            const imageText = productElement.querySelector('ul > li.goods_img > a > img');
            const image = imageText.getAttribute('src');
            const hrefSelector = productElement.querySelector('a').getAttribute('onclick');
            const href = 'https://www.metaldiy.com/item/itemView.do?itemId=' + hrefSelector.match(/\d+/g).join('');
            const platform = '철물박사';
            productsArr.push({ name, price, image, href, platform });
        }
        return productsArr;
    });
}
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    // page.setDefaultNavigationTimeout(0);
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password, curPage] = args;
        const pageURL = '&currentPageNo=' + curPage;
        const fullURL = listURL + pageURL;
        await page.goto('https://www.metaldiy.com/login/popupLogin.do?popupYn=Y');
        await page.waitForSelector('#loginId');
        await page.waitForSelector('#loginPw');
        await page.type('#loginId', username);
        await page.type('#loginPw', password);
        await page.waitForSelector('input[title="로그인"]');
        await page.click('input[title="로그인"]');
        await page.waitForNavigation();
        // 웹 페이지로 이동
        await page.goto(fullURL);
        await page.waitForSelector('#container > div.container.wrapper_fix > div.goods_list_contents > h3 > strong');
        const allProducts = await extractProductsOnPage(page);
        // 상품 정보 출력
        console.log(JSON.stringify(allProducts));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
