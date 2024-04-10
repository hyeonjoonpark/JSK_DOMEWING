const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
    await page.goto('https://www.ibiskorea.com/shop/member.html?type=login', { waitUntil: 'networkidle0' });
    await page.type('#loginWrap > div > div > div.mlog > form > fieldset > ul > li.id > input', username);
    await page.type('#loginWrap > div > div > div.mlog > form > fieldset > ul > li.pwd > input', password);
    await page.click('#loginWrap > div > div > div.mlog > form > fieldset > a > img');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#prdBrand > div.item-wrap > div.item-info > div > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 40;
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
        const productElements = document.querySelectorAll('dl.item-list');

        function checkSkipProduct(promotionElement) {
            const skipImages = ["/shopimages/ibis/prod_icons/3664?1595289056"];
            const promotionSrc = promotionElement.getAttribute('src');
            return skipImages.includes(promotionSrc);
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('img.MK-product-icon-2');
            if (promotionElement && checkSkipProduct(promotionElement)) {
                continue; // Skip if sold out, matches the custom promotion image, or is marked as wholesale
            }

            const nameElement = productElement.querySelector('dd > ul > li.prd-name');
            const imageElement = productElement.querySelector('img.MS_prod_img_m');
            const priceElement = productElement.querySelector('dd > ul > li.prd-price > span.prod_dis_info');
            const hrefElement = productElement.querySelector('div > dl > dt > a');

            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const platform = "아이비스코리아";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}


