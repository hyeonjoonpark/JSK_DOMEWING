const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
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
    await page.goto('https://bestlight.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a > img');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#sunny_contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-normalmenu > div > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 50;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    if (listUrl.includes('&page=')) {
        const urlSplit = listUrl.split('&');
        listUrl = urlSplit[0] + '&page=' + curPage;
    }
    else listUrl += '&page=' + curPage;

    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#sunny_contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal.ec-base-product > ul > li.xans-record-');
        for (const productElement of productElements) {

            const promotionElement = productElement.querySelector('div > div.status > div > img');
            if (promotionElement) {
                if (checkSkipProduct(promotionElement)) {
                    continue;
                }
            }
            const nameElement = productElement.querySelector('div > div.name2 > a > span');
            const imageElement = productElement.querySelector('div > a > img');
            const priceElement = productElement.querySelector('div > div.name2 > ul > li:nth-child(2) > span:nth-child(1)');
            const priceElement2 = productElement.querySelector('div > div.name2 > ul > li > span:nth-child(1)');
            const hrefElement = productElement.querySelector('div > div.name2 > a');



            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : priceElement2.textContent.trim().replace(/[^\d]/g, '');
            const platform = "베스트조명";
            products.push({ name, price, image, href, platform });
        }
        return products;

        function checkSkipProduct(promotionElement) {
            const soldOut = "/web/upload/icon_201702241707401700.jpg";
            const promotionSrc = promotionElement.getAttribute('src');
            if (promotionSrc.includes(soldOut)) {
                return true;
            }
            return false;
        }
    });
    return products;
}





