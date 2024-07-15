const puppeteer = require('puppeteer');
const { goToAttempts } = require('./common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listUrl] = process.argv.slice(2);
    try {
        await goToAttempts(page, listUrl, 'domcontentloaded');
        const numPage = await getNumPage(page, listUrl);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, listUrl, i);
            let list = await getListProducts(page);
            products.push(...list);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#CategoryProducts > div > div > div > ul > li > div > span > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 40;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    listUrl += '&page=' + curPage;
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
}

async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('#CategoryProducts > ul li');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const isSoldOut = pe.querySelectorAll('#CategoryProducts > ul > li > div > a > div._20THe-Uu6x > span > span');
            if (isSoldOut.length > 0) {
                return false;
            }
            const nameElement = pe.querySelector('#CategoryProducts > ul > li > div > a > strong');
            if (!nameElement) {
                return false;
            }
            const priceElements = pe.querySelectorAll('#CategoryProducts > ul > li > div > a > div._1zl6cBsmdy > strong > span');
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }
            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }
            const imageElement = pe.querySelector('#CategoryProducts > ul > li > div > a > div._3GC6Xcq6fT > div > div > div > img');
            if (!imageElement) {
                return false;
            }
            const hrefElement = pe.querySelector('#CategoryProducts > ul > li > div > a');
            if (!hrefElement) {
                return false;
            }
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            const href = 'https://smartstore.naver.com/yphm' + hrefElement.getAttribute('href');
            const platform = '와이피';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
