const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        await page.goto(listURL, { waitUntil: 'networkidle0' });
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();



async function signIn(page, username, password) {
    await page.goto('https://m2b.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > div.login__button > a');
    await page.waitForNavigation();
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('div.prdList__item');

        function checkSkipProduct(promotionElement) {
            const soldOut = "//img.echosting.cafe24.com/design/common/icon_sellout.gif";
            const promotionSrc = promotionElement.getAttribute('src');
            if (promotionSrc == soldOut) {
                return true;
            }
            return false;
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('div > div.description > div.icon > img');
            if (promotionElement) {
                if (checkSkipProduct(promotionElement)) {
                    continue;
                }
            }

            const nameElement = productElement.querySelector('div > div.description > div.name > a > span:nth-child(2)');
            const imageElement = productElement.querySelector('div.prdImg img');
            const priceElement = productElement.querySelector('ul > li.product_custom.xans-record- > span');
            const hrefElement = productElement.querySelector('div > div.thumbnail > div.prdImg > a');

            // removeSoldOutMessage 함수 호출을 제거함
            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "엠투비";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}
