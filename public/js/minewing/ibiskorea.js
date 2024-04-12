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
        const skipImages = ["/shopimages/ibis/prod_icons/3664?1595289056"];

        function checkSkipProduct(promotionElement) {
            const promotionSrc = promotionElement.getAttribute('src');
            return skipImages.includes(promotionSrc);
        }

        function extractThirdText(priceElement) {
            const textNodes = Array.from(priceElement.childNodes).filter(node => node.nodeType === Node.TEXT_NODE);
            if (textNodes.length < 3) {
                return null; // 세 번째 텍스트 노드가 없으면 null 반환
            }
            return textNodes[2].nodeValue.trim().replace(/[^\d]/g, ''); // 세 번째 텍스트 노드에서 숫자만 추출
        }

        for (const productElement of productElements) {
            const promotionElement = productElement.querySelector('img.MK-product-icon-2');
            if (promotionElement && checkSkipProduct(promotionElement)) {
                continue; // Skip if sold out or matches the custom promotion image
            }

            const nameElement = productElement.querySelector('dd > ul > li.prd-name');
            const imageElement = productElement.querySelector('img.MS_prod_img_m');
            const priceElement = productElement.querySelectorAll('dd > ul > li.prd-price')[1]; // 두 번째 'prd-price' 요소
            const hrefElement = productElement.querySelector('div > dl > dt > a');

            // 세 번째 텍스트 노드를 추출
            const price = priceElement ? extractThirdText(priceElement) : null;
            if (!price) {
                continue; // 세 번째 텍스트가 없으면 이 제품을 건너뛰기
            }

            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const platform = "아이비스코리아";

            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}

