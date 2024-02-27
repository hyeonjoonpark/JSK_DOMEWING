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
    await page.goto('https://candle-box.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('a[class="btnLogin"]');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#Product_ListMenu > p').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const numPerPage = 20;
    const numPage = Math.ceil(numProducts / numPerPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    listUrl += '?page=' + curPage;
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal.ec-base-product > ul > li');
        for (const productElement of productElements) {

            const promotionElement = productElement.querySelector('.description .promotion img');
            if (promotionElement) {
                if (checkSkipProduct(promotionElement)) {
                    continue;
                }
            }

            const nameElement = productElement.querySelector('div.description > strong > a > span:nth-child(2)');
            const imageElement = productElement.querySelector('div.thumbnail > div.prdImg > a > img');
            const priceElement = productElement.querySelector('div.description > ul > li > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div.description > strong > a');

            const name = nameElement ? removeSoldOutMessage(nameElement.textContent) : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "캔들아트";
            products.push({ name, price, image, href, platform });
        }
        return products;
        function removeSoldOutMessage(nameElement) {
            const productName = nameElement.trim();
            if (productName.includes('-품절시 단종')) {
                return productName.replace('-품절시 단종', '');
            }
            return productName;
        }
        function checkSkipProduct(promotionElement) {
            // const thirtyPerDiscount = "/web/upload/custom_13.gif";
            // const tenPerDiscount = "/web/upload/custom_24.gif";
            // const flashDeal = "//img.echosting.cafe24.com/icon/product/ko_KR/icon_05_01.gif";
            const soldOut = "//img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif";
            // const newProductDiscount = "/web/upload/custom_3.gif";
            const promotionSrc = promotionElement.getAttribute('src');
            // if (promotionSrc == thirtyPerDiscount || promotionSrc == tenPerDiscount || promotionSrc == flashDeal || promotionSrc == soldOut || promotionSrc == newProductDiscount) {
            //     return true;
            // }
            if (promotionSrc == soldOut) {
                return true;
            }
            return false;
        }
    });
    return products;
}





