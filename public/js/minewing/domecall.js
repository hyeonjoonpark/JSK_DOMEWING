const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        // const [listURL, username, password] = args;
        // await signIn(page, username, password);
        const username = 'sungil2022';
        const password = 'tjddlf88!@';
        const listURL = 'https://www.domecall.net/goods/goods_list.php?cateCd=076';

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
        // await browser.close();
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
            const soldOut = productElement.querySelector('div > div.thumbnail > a > span.soldout-img');
            if (soldOut) {
                continue;
            }
            const nameElement = productElement.querySelector('div > div.txt > a > strong');
            const imageElement = productElement.querySelector('div > div.thumbnail > a > img');
            const priceElement = productElement.querySelector('div > div.price.gd-default > span > strong');
            const hrefElement = productElement.querySelector('div > div.txt > a').href.trim();
            const baseUrl = 'https://www.domecall.net';
            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? makeSafetyUrl(baseUrl, hrefElement) : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "도매콜";
            products.push({ name, price, image, href, platform });
        }
        return products;

        function makeSafetyUrl(baseUrl, href) {
            let safetyUrl = '';
            if (href.startsWith('../')) {
                hrefElement = href.slice(2);
                safetyUrl = baseUrl + hrefElement;
            }
            else safetyUrl = baseUrl + href;
            return safetyUrl;
        }
    });


}





