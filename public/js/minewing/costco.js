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
    await page.goto('https://www.costco.co.kr/login', { waitUntil: 'networkidle0' });
    await page.type('#j_username', username);
    await page.type('#j_password', password);
    await page.click('#loginSubmit');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const url = await page.evaluate((listURL) => {
        const numTotal = parseInt(document.querySelector("#b_white > font > b").textContent.trim().replace(/[^\d]/g, ''));
        listURL += '&page_num=' + numTotal;
        return listURL;
    }, listURL);
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('td[align="center"][valign="top"]');
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
                const soldOutImageElement = productElement.querySelector('img[src="/shop/data/skin/everybag/img/icon/good_icon_soldout.gif"]');
                if (soldOutImageElement) {
                    return false;
                }
                const name = productElement.querySelector('div:nth-child(2) > div:nth-child(1) > a').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const price = parseInt(productElement.querySelector('div[style="padding-bottom:3px; font-family:Tahoma, Geneva, sans-serif; font-size:12px; font-weight:bold; color:#ed5d55;"] > b').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('div:nth-child(1) > a > img').src;
                const href = productElement.querySelector('div:nth-child(1) > a').href;
                const platform = "코스트코";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
