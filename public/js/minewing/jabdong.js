const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, listURL, i);
            let list = await scrapeProducts(page);
            products.push(...list);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        // await browser.close();
    }
})();


async function signIn(page, username, password) {
    await page.goto('https://jabdong.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div.login > fieldset > a img');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-normalmenu > div > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const numPerPage = 20;
    const numPage = Math.ceil(numProducts / numPerPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    listUrl += '&page=' + curPage;
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal > ul > li');
        for (const productElement of productElements) {

            const promotionElement = productElement.querySelector('div.box > div.status > div.icon img');
            if (promotionElement) {
                if (checkSkipProduct(promotionElement)) {
                    continue;
                }
            }
            const nameElement = productElement.querySelector('div > p > a > span:nth-child(2)');

            const imageElement = productElement.querySelector('div.box > a img');
            const priceElement = productElement.querySelector('div > ul > li:nth-child(1) > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div > p > a');

            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "잡동산이";
            products.push({ name, price, image, href, platform });
        }
        return products;

        function checkSkipProduct(promotionElement) {
            const soldOut = "//img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif";
            const promotionSrc = promotionElement.getAttribute('src');
            if (promotionSrc == soldOut) {
                return true;
            }
            return false;
        }
    });
    return products;
}





