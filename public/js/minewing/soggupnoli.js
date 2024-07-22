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
    await page.goto('https://soggupnoli.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.member_login_box > div.login_input_sec > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function moveToPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'load' });
    const url = await page.evaluate((listURL) => {
        const numTotal = parseInt(document.querySelector("#contents > div > div > div.goods_list_item > div.goods_pick_list > span > strong").textContent.trim().replace(/[^\d]/g, ''));
        listURL += '&sort=&pageNum=' + numTotal;
        return listURL;
    }, listURL);
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('div.item_cont');
        const products = [];
        productElements.forEach((productElement) => {
            const product = scrapeProduct(productElement, forbiddenWords);
            if (product) {
                products.push(product);
            }
        });
        return products;

        function scrapeProduct(productElement, forbiddenWords) {
            try {
                const soldOutImageElement = productElement.querySelector('img[src="https://cdn-pro-web-250-118.cdn-nhncommerce.com/sogguptr0066_godomall_com/data/icon/goods_icon/icon_soldout.gif"]');
                if (soldOutImageElement) {
                    return false;
                }
                const name = productElement.querySelector('strong.item_name').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                const priceElement = productElement.querySelector('strong.item_price > span');
                let priceText = '';
                priceElement.childNodes.forEach((node) => {
                    priceText += node.textContent.trim();
                });
                const price = parseInt(priceText.replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('div > div.item_photo_box > a > img').src;
                const href = productElement.querySelector('div > div.item_photo_box > a').href;
                const platform = "소꿉노리";
                return { name, price, image, href, platform };
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
