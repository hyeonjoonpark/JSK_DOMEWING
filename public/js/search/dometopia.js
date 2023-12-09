const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const keyword = process.argv[2];
        // 웹 페이지로 이동
        await page.goto('https://dometopia.com/member/login');
        await page.waitForSelector('#userid');
        await page.waitForSelector('#password');
        await page.type('#userid', 'luminous2020');
        await page.type('#password', 'Fnalshtm88!@');
        await page.waitForSelector('input[type="submit"]');
        await page.click('input[type="submit"]');
        await page.waitForNavigation();
        await page.goto('https://dometopia.com/goods/search?search_text=' + keyword);
        async function wait(seconds) {
            return new Promise(resolve => setTimeout(resolve, seconds * 1000));
        }

        // 5초 동안 기다린 후 코드를 진행
        // await wait(5);

        // 상품 정보 추출
        const products = await page.evaluate(() => {
            const productElements = document.querySelectorAll('td[style="position: relative;"][valign="top"]');
            const productsArr = [];

            for (const productElement of productElements) {
                const nameQuery = productElement.querySelector('dl > dd.goodsDisplayTitle > div > a > h6');
                const priceQuery = productElement.querySelector('dl > dd.goodsDisplaySalePrice.clfix > div > table > tbody > tr > td.price_num');
                const imageQuery = productElement.querySelector('dl > dt > span > a > img');
                const hrefQuery = productElement.querySelector('dl > dt > span > a');
                if (nameQuery != null && priceQuery != null && imageQuery != null && hrefQuery != null) {
                    const nameText = productElement.querySelector('dl > dd.goodsDisplayTitle > div > a > h6').textContent;
                    const name = nameText;
                    const priceText = productElement.querySelector('dl > dd.goodsDisplaySalePrice.clfix > div > table > tbody > tr > td.price_num').textContent;
                    const priceNumber = priceText.match(/\d+/g);
                    const price = parseInt(priceNumber.join(''), 10);
                    const imageText = productElement.querySelector('dl > dt > span > a > img');
                    const image = imageText.getAttribute('src');
                    const hrefText = productElement.querySelector('dl > dt > span > a').getAttribute('href');
                    const href = "https://dometopia.com" + hrefText;
                    const platform = '도매토피아';
                    productsArr.push({ name, price, image, href, platform });
                }
            }

            return productsArr;
        });

        // 상품 정보 출력
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('오류 발생:', error);
    } finally {
        await browser.close();
    }
})();
