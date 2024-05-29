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
        await browser.close();
    }
})();


async function signIn(page, username, password) {
    await page.goto('https://dadeshop.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 12;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    const url = new URL(listUrl);
    url.searchParams.set('page', curPage);

    await page.goto(url.toString(), { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('div.xans-element-.xans-product.xans-product-listnormal.ec-base-product > ul.prdList.grid4 > li.xans-record-');

        function checkSkipProduct(promotionElement) {
            const skipImageUrl = "https://cafe24.poxo.com/ec01/haven96/3JPAsJn/jGkesyYvH/tEadcPSdLJM/sODBCcZoIMNBPMyLs3Fh/UhKUCF9ppWck1AnJzNV609EfxIBkrnZmHxg==/_/web/upload/icon_201909061817097000.png";
            const promotionSrc = promotionElement.getAttribute('src');
            return promotionSrc === skipImageUrl;
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('div.icon > img');
            if (promotionElement && checkSkipProduct(promotionElement)) {
                continue;
            }

            const nameElement = productElement.querySelector('div.description > p > a');
            const imageElement = productElement.querySelector('div.thumbnail [id^="eListPrdImage"]');
            const priceElement = productElement.querySelector('div.description > ul > li:nth-child(1) > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div.thumbnail > div > a');

            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const name = nameElement ? nameElement.textContent.trim()
                .replace(/[A-Za-z]{2}-[A-Za-z0-9]+/g, '')
                .replace('상품명 :', '')
                .trim() : 'Name not found';
            const platform = "다데샵";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}



