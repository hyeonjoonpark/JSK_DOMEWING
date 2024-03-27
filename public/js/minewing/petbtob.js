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
    await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#Product_ListMenu > div > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 32;
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
        const productElements = document.querySelectorAll('li.item,DB_rate xans-record-');

        function checkSkipProduct(promotionElement) {
            const soldOut = "//img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif";
            const promotionSrc = promotionElement.getAttribute('src');
            if (promotionSrc == soldOut) {
                return true;
            }
            return false;
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('div.description > div.status > div > img');
            if (promotionElement && checkSkipProduct(promotionElement)) {
                continue; // Skip if sold out
            }

            const nameElement = productElement.querySelector('div.description > p.name > a');
            const imageElement = productElement.querySelector('div.thumbnail.outline > a > div.normal_thumb > img');
            const priceElement = productElement.querySelector('div.description > ul > li:nth-child(2) > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div.thumbnail.outline > a');

            if (!nameElement) continue; // Skip if no name element
            const name = nameElement.textContent.trim();
            // Skip if name contains "오프라인" or "판매금지"
            if (name.includes("오프라인") || name.includes("판매금지")) continue;

            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "펫비투비";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}
