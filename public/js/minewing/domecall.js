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
    await page.goto('https://www.domecall.net/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#content > div.contents > div > div.cg-main > div.goods-list > span > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 40;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    const listUrlSplit = listUrl.split('?');
    const newUrl = listUrlSplit[0] + '?page=' + curPage + '&' + listUrlSplit[1] + '&sort=g.regDt%20desc&pageNum=40';
    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#content > div.contents > div > div.cg-main > div.goods-list > div > div > ul li');
        for (const productElement of productElements) {
            const nameElement = productElement.querySelector('div > div.txt > a > strong').textContent.trim();
            if (checkSkipProduct(productElement, nameElement)) {
                continue;
            }
            const imageElement = productElement.querySelector('div > div.thumbnail > a > img');
            const priceElement = productElement.querySelector('div > div.price.gd-default > span > strong');
            const hrefElement = productElement.querySelector('div > div.txt > a').href.trim();

            const name = nameElement ? imageElement : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? makeSafetyUrl(hrefElement) : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "도매콜";
            products.push({ name, price, image, href, platform });
        }
        return products;


        function makeSafetyUrl(href) {
            let safetyUrl = '';
            if (href.startsWith('../')) {
                hrefElement = href.slice(2);
                safetyUrl = hrefElement;
            }
            else safetyUrl = href;
            return safetyUrl;
        }
        function checkSkipProduct(productElement, nameElement) {
            if (nameElement.includes('매장판매') || nameElement.includes('차량배송')) {
                return true;
            }
            const seasonImage = "https://cdn-pro-web-134-253.cdn-nhncommerce.com/alllatr4832_godomall_com/data/icon/goods_icon/my_icon_160282633410.jpg";
            const noReturnImage = "https://cdn-pro-web-134-253.cdn-nhncommerce.com/alllatr4832_godomall_com/data/icon/goods_icon/my_icon_16028262519.jpg";
            const deliverImage = "/data/icon/goods_icon/차량배송.jpg"
            const expirationDateImage = "/data/icon/goods_icon/유통기한.jpg";

            const soldOutImage = productElement.querySelector('div > div.thumbnail > a > span.soldout-img');
            const soldOut = soldOutImage ? soldOutImage.textContent.trim() : null;
            if (soldOut !== null) {
                return true;
            }

            const productSkipImages = productElement.querySelectorAll('div > div.thumbnail > a > span > img');
            for (const productSkipImage of productSkipImages) {
                const productimage = productSkipImage.src.trim();
                if ((productimage == seasonImage) || (productimage == noReturnImage) || (productimage == deliverImage) || (productimage == expirationDateImage)) {
                    return true;
                }
            }
            return false;
        }

    });
    return products;
}





