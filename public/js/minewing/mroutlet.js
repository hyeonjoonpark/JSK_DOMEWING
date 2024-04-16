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
    await page.goto('https://mroutlet.cafe24.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-normalmenu > div > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 20; // 페이지당 상품 수
    const numPage = Math.ceil(numProducts / countProductInPage); // 전체 페이지 수 계산
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    const url = new URL(listUrl);
    url.searchParams.set('page', curPage); // 페이지 번호를 현재 페이지로 설정

    await page.goto(url.toString(), { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('ul.romi_prdList.itemline4 > li.item.xans-record- div');

        function checkSkipProduct(promotionElement) {
            const soldOut = "/web/upload/icon_201806150348451600.png";
            const promotionSrc = promotionElement.getAttribute('src');
            if (promotionSrc == soldOut) {
                return true;
            }
            return false;
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('ul.romi_prdList.itemline4 > li.item.xans-record- > div > p.icon > img');
            if (promotionElement) {
                if (checkSkipProduct(promotionElement)) {
                    continue;
                }
            }

            const nameElement = productElement.querySelector('ul.romi_prdList.itemline4 > li.item.xans-record- > div > p.name > a > span');
            const imageElement = productElement.querySelector('ul.romi_prdList.itemline4 > li.item.xans-record- > div > a > img');
            const priceElement = productElement.querySelector('ul.romi_prdList.itemline4 > li.item.xans-record- > div > ul > li:nth-child(1) > span:nth-child(2)');
            const hrefElement = productElement.querySelector('ul.romi_prdList.itemline4 > li.item.xans-record- > div > a');

            // removeSoldOutMessage 함수 호출을 제거함
            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "미래아울렛";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}
