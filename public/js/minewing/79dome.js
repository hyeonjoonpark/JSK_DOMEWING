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
    await page.goto('https://www.79dome.com/shop/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(3) > td:nth-child(2) > input[type=password]', password);
    await page.click('#form > table > tbody > tr:nth-child(2) > td.noline > input[type=image]');
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
        const productElements = document.querySelectorAll('div.indiv > form > table:nth-child(8) > tbody > tr:nth-child(5) > td > table > tbody > tr td');
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
                const soldOutImageElement = productElement.querySelector('img[src*="soldout.gif"]');
                if (soldOutImageElement) {
                    return false;
                }
                const name = productElement.querySelector('div > div > a').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const price = parseInt(productElement.querySelector('div > div > b').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('div > a > img').src;
                const href = productElement.querySelector('body > table > tbody > tr:nth-child(2) > td > table > tbody > tr > td.outline_side > div.indiv > form > table > tbody > tr > td > table > tbody > tr > td > div > a').href;
                const platform = "친구도매";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
