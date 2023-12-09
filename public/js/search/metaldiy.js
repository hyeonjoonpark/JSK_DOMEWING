const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const keyword = process.argv[2];
        await page.goto('https://www.metaldiy.com/login/popupLogin.do?popupYn=Y');
        await page.waitForSelector('#loginId');
        await page.waitForSelector('#loginPw');
        await page.type('#loginId', 'sungil2018');
        await page.type('#loginPw', 'tjddlf88!@');
        await page.waitForSelector('input[title="로그인"]');
        await page.click('input[title="로그인"]');
        await page.waitForNavigation();
        // 웹 페이지로 이동
        await page.goto('https://www.metaldiy.com/main/searchItemList.do?kw=' + keyword);

        await page.waitForSelector('#container > div.container.wrapper_fix > div.goods_list_contents > div.search_lists_area > ul');

        // 상품 정보 추출
        const products = await page.evaluate(() => {
            const productElements = document.querySelectorAll('ul[class="goodsBox"] > li');
            const productsArr = [];

            for (const productElement of productElements) {
                const nameText = productElement.querySelector('ul > li.goods_md > a').textContent;
                const name = nameText;
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

        // 상품 정보 출력
        console.log(JSON.stringify(products));
    } catch (error) {
        console.log(JSON.stringify(error));
    } finally {
        await browser.close();
    }
})();
