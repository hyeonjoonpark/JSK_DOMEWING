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
    await page.goto('https://thepetmart.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > div.login_btn');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-normalmenu > div > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 24;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}


async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    let newUrl = '';
    if (listUrl.includes('&page=')) {
        const urlSplit = listUrl.split('&');
        newUrl = urlSplit[0] + '&page=' + curPage;
    }
    else newUrl = listUrl + '&page=' + curPage;

    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal > ul li.item[class*="xans-record-"]');

        for (const productElement of productElements) {
            const skipChecks = productElement.querySelectorAll('div.box > div > div.icon img');

            if (checkSkipProduct(skipChecks)) {
                continue;
            }

            const minSalePriceLimit = productElement.querySelector('div.box > p > a > span > b > font');
            if (minSalePriceLimit) {
                if (minSalePriceLimit.textContent.includes('판매금지')) continue;
            }
            const nameElement = productElement.querySelector('div.box > p > a > span');
            const imageElement = productElement.querySelector('div.box > a > img');
            const priceElement = productElement.querySelector('div.box > ul > li > span:nth-child(2)');
            const hrefElement = productElement.querySelector('div.box > a');

            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "더펫마트";
            products.push({ name, price, image, href, platform });
        }
        return products;
        function checkSkipProduct(imageElements) {
            const skipImages = [
                { url: 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif', description: '품절' },
                { url: 'https://thepetmart.co.kr/web/upload/custom_1516384969854800.gif', description: '온라인 판매 불가' },
                { url: 'https://thepetmart.co.kr/web/upload/custom_11.gif', description: '임시 품절' }
            ];

            for (const imageElement of imageElements) {
                if (skipImages.some(skipImage => skipImage.url === imageElement.src.trim())) {
                    return true;
                }
            }
            return false;
        }

    });
    return products;
}




