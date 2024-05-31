const { downloadBrowser } = require('puppeteer/internal/node/install.js');
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
        const products = await scrapeProducts(page, forbiddenWords);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://www.lanmart.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(1) > input', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td > input', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const url = await page.evaluate((listURL) => {
        const numTotal = parseInt(document.querySelector("#content > div > form:nth-child(3) > p > span:nth-child(2)").textContent.trim().replace(/[^\d]/g, ''));
        listURL += '&page_num=' + numTotal;
        return listURL;
    }, listURL);
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('td > div.goodsDisplay.w_1100 > ul li');
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
                const soldOutImageElement = productElement.querySelector('div img.el-goods-soldout-image');
                if (soldOutImageElement) {
                    return false;
                }
                const name = productElement.querySelector('a.pname.ff_noto').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const price = parseInt(productElement.querySelector('a.pprice').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('li > div > a > img').src;

                // 주석 처리된 변경 사항: 특정 이미지 경로를 확인하고 그 이미지가 있을 경우 제품을 건너뜁니다.
                const forbiddenImage = '../data/my_icon/my_icon_14906635858.png';
                if (image === forbiddenImage) {
                    return false; // 특정 이미지가 있는 경우 false를 반환하여 제품을 건너뜁니다.
                }

                const href = productElement.querySelector('li > div > a.goodsDisplay_a').href;
                const platform = "랜마트";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
