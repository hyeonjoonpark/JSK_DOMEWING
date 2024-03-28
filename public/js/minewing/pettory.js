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
    await page.goto('https://pettory.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div.login > fieldset > a');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-normalmenu > div > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 36;
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
        const productElements = document.querySelectorAll('#contents .xans-element-.xans-product.xans-product-listnormal.ec-base-product ul [id^="anchorBoxId_"]');

        function checkSkipProduct(promotionElement) {
            const skipImages = [
                "//img.echosting.cafe24.com/design/common/icon_sellout.gif",
                "/web/upload/custom_4516817090408834.gif"
            ];
            const promotionSrc = promotionElement.getAttribute('src');
            return skipImages.includes(promotionSrc);
        }

        function isWholesale(productElement) {
            const wholesaleText = productElement.querySelector('div.description > ul > li:nth-child(2) > span');
            return wholesaleText && wholesaleText.textContent.includes("타도매");
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('div.description > div.icon > div.promotion.cboth > img');
            if (promotionElement && checkSkipProduct(promotionElement) || isWholesale(productElement)) {
                continue; // Skip if sold out, matches the custom promotion image, or is marked as wholesale
            }

            const nameElement = productElement.querySelector('div.description > strong > a > span:nth-child(2)');
            const imageElement = productElement.querySelector('div.thumbnail [id^="eListPrdImage"]');
            const priceElement = productElement.querySelector('div.description > ul > li:nth-child(1) > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div.thumbnail > a');

            const name = nameElement.textContent.trim();
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "펫토리";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}


