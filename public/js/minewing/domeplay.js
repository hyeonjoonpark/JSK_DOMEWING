const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        await page.goto(listURL, { waitUntil: 'networkidle0' });
        await autoScroll(page);  // 페이지를 끝까지 스크롤합니다.
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function signIn(page, username, password) {
    await page.goto('https://domeplay.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('#member_login_module_id > fieldset > div.login__button > a.btnSubmit.gFull.sizeL');
    await page.waitForNavigation();
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('ul.prdList.grid5 > li div.prdList__item');

        function checkSkipProduct(promotionElement) {
            const soldOut = "//img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif";
            const promotionSrc = promotionElement.getAttribute('src');
            return promotionSrc == soldOut;
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('div > div.description > div.icon > img');
            if (promotionElement && checkSkipProduct(promotionElement)) {
                continue;
            }

            const nameElement = productElement.querySelector('div > div.description > div.name > a > span:nth-child(2)');
            if (!nameElement || nameElement.textContent.trim().includes("준수")) {
                continue; // 상품명에 "준수"가 포함된 경우 건너뜁니다.
            }

            const imageElement = productElement.querySelector('div.thumbnail > a > img');
            const priceElement = productElement.querySelector('div > div.description > ul > li:nth-child(2) > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div > div.thumbnail > a');

            const name = nameElement.textContent.trim();
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "도매플레이";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}


// 페이지를 끝까지 스크롤하는 함수 추가
async function autoScroll(page) {
    await page.evaluate(async () => {
        await new Promise((resolve, reject) => {
            var totalHeight = 0;
            var distance = 100;
            var maxScrollAttempts = 500; // 최대 스크롤 시도 횟수
            var attempts = 0;

            var timer = setInterval(() => {
                var scrollHeight = document.body.scrollHeight;
                window.scrollBy(0, distance);
                totalHeight += distance;

                if (totalHeight >= scrollHeight) {
                    clearInterval(timer);
                    resolve();
                } else {
                    attempts++;
                    if (attempts > maxScrollAttempts) {
                        clearInterval(timer);
                        reject('Scroll failed after maximum attempts');
                    }
                }
            }, 100);
        });
    });
}
