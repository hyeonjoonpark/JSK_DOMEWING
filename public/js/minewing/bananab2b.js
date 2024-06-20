const getForbiddenWords = require('../forbidden_words');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password);
        await moveToPage(page, listURL);
        const forbiddenWords = getForbiddenWords();
        await autoScroll(page); // 자동 스크롤 함수 호출
        const products = await scrapeProducts(page, forbiddenWords);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://bananab2b.shop/login?redirect=/', { waitUntil: 'networkidle0' });
    await page.type('input[class="TextField_input__hOlLE"]', username);
    await page.type('input[type="password"]', password);
    await page.click('#__next > div.PageLayout_layout-container__dJ80A.PageLayout_topBanner__r3_gf.PageLayout_top__9MJc2.PageLayout_header__vbEvv.PageLayout_gnb__Yvsel > div.PageLayout_content__WCS1_ > div > section > div > form > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'networkidle2' });
}
async function autoScroll(page) {
    await page.evaluate(async () => {
        await new Promise((resolve) => {
            let totalHeight = 0;
            const distance = 100;
            const timer = setInterval(() => {
                window.scrollBy(0, distance);
                totalHeight += distance;

                if (totalHeight >= document.body.scrollHeight) {
                    clearInterval(timer);
                    resolve();
                }
            }, 100);
        });
    });
}
async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('#__next > div.PageLayout_layout-container__dJ80A.PageLayout_topBanner__r3_gf.PageLayout_top__9MJc2.PageLayout_header__vbEvv.PageLayout_gnb__Yvsel > div.PageLayout_content__WCS1_ > div > div.FilterList_list-grid__mdHy_ > div > div');
        const products = [];
        for (const productElement of productElements) {
            const product = scrapeProduct(productElement, forbiddenWords);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        return products;
        function scrapeProduct(productElement, forbiddenWords) {
            try {
                const soldOutImageElements = productElement.querySelectorAll('.ProductCard_tag-list__fg5se > span');
                for (const element of soldOutImageElements) {
                    if (element.textContent.trim() === '폐쇄몰') {
                        return false;
                    }
                }

                const name = productElement.querySelector('.ProductCard_title__vWQYZ').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const price = parseInt(productElement.querySelector('.ProductCard_price__yXDdU > .ProductCard_number-700__sjcfS').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('.ProductCard_thumbnail__06Rgr > img').src;
                const regex = /%2F(\d+)%2F/;
                const productCodeMatch = image.match(regex);
                const baseUrl = 'https://bananab2b.shop/product/';
                const href = baseUrl + productCodeMatch[1];
                const platform = "바나나비투비";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
