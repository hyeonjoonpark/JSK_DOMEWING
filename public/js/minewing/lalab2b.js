const getForbiddenWords = require('../forbidden_words');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
    await page.goto('https://www.lalab2b.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const url = await page.evaluate((listURL) => {
        const numTotal = parseInt(document.querySelector("#content > div.contents > div > div.cg-main > div.goods-list > form:nth-child(1) > fieldset > div > span > strong").textContent.trim().replace(/[^\d]/g, ''));
        listURL += '&sort=&pageNum=' + numTotal;
        return listURL;
    }, listURL);
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('#content > div.contents > div > div.cg-main > div.goods-list > div.item-display.type-gallery > div > ul li');
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
                const soldOutImageElement = productElement.querySelector('iv > div.txt > div > img');
                if (soldOutImageElement) {
                    return false;
                }
                const name = productElement.querySelector('strong.prdName').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const price = parseInt(productElement.querySelector('div > div.price.gd-default > span.cost.prdPrice > span > strong').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('div > div.thumbnail > a > img').src;
                const href = productElement.querySelector('div > div.thumbnail > a').href;
                const platform = "라라비투비";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
