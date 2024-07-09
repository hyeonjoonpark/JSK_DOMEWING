const puppeteer = require('puppeteer');
const { goToAttempts, signIn } = require('./common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listUrl, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password, 'https://domewang.co.kr/member/login.html', '#member_id', '#member_passwd', 'div > div > fieldset > a > img');
        await goToAttempts(page, listUrl, 'domcontentloaded');
        const lastPageNumber = await getLastPageNumber(page);
        const products = [];
        for (let i = lastPageNumber; i > 0; i--) {
            await goToAttempts(page, listUrl + '&page=' + i, 'domcontentloaded');
            const listProducts = await getListProducts(page);
            products.push(...listProducts);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function getLastPageNumber(page) {
    const lastPageNumber = await page.evaluate(() => {
        const lastPageUrl = document.querySelector('p.last > a').getAttribute('href');
        const urlParams = new URLSearchParams(lastPageUrl);
        const pageValue = urlParams.get('page');
        return pageValue;
    });
    return lastPageNumber ? parseInt(lastPageNumber) : 1;
}
async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('ul.prdList li');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const isSoldOut = Array.from(pe.querySelectorAll('div.icon img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
            if (isSoldOut) {
                return false;
            }
            const nameElement = pe.querySelector('p.name > strong > a > span:nth-child(2)');
            if (!nameElement) {
                return false;
            }
            const priceElements = pe.querySelectorAll('span[style="font-size:12px;color:#008BCC;font-weight:bold;"]');
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }
            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }
            const imageElement = pe.querySelector('p.prdImg > a > img')
            if (!imageElement) {
                return false;
            }
            const hrefElement = pe.querySelector('p.prdImg > a');
            if (!hrefElement) {
                return false;
            }
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            const href = 'https://domewang.co.kr' + hrefElement.getAttribute('href');
            const platform = '도매왕';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
