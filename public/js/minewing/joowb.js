const puppeteer = require('puppeteer');
const { goToAttempts, signIn } = require('./common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listUrl, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password, 'https://joowb.com/member/login.html', '#member_id', '#member_passwd', 'div > fieldset > a.btnSubmit.sizeL.df-lang-button-login');
        await goToAttempts(page, listUrl, 'domcontentloaded');
        const lastPageNumber = await getLastPageNumber(page);
        const products = [];
        for (let i = lastPageNumber; i > 0; i--) {
            await goToAttempts(page, listUrl + '&page=' + i, 'domcontentloaded');
            const listProducts = await getListProducts(page);
            console.log(listProducts);
            products.push(...listProducts);
            console.log(products.length);
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
        const lastPageUrl = document.querySelector('a.last').getAttribute('href');
        const urlParams = new URLSearchParams(lastPageUrl);
        const pageValue = urlParams.get('page');
        return pageValue;
    });
    return lastPageNumber ? parseInt(lastPageNumber) : 1;
}
async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal.df-prl-wrap.df-prl-setup > ul > li');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const isSoldOut = Array.from(pe.querySelectorAll('div.df-prl-icon > img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
            if (isSoldOut) {
                return false;
            }
            const nameElement = pe.querySelector('div > div.df-prl-desc > div > a > span');
            if (!nameElement) {
                return false;
            }
            const priceElements = pe.querySelectorAll('div > div.df-prl-desc > div > ul > li.a-limited-price.df-prl-listitem-cell.product_price.xans-record- > span:nth-child(2)');
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }
            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }
            const imageElement = pe.querySelector('div > div.df-prl-thumb > a > img')
            if (!imageElement) {
                return false;
            }
            const hrefElement = pe.querySelector('div > div.df-prl-thumb > a');
            if (!hrefElement) {
                return false;
            }
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            const href = 'https://joowb.com/' + hrefElement.getAttribute('href');
            const platform = '엄마애손';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
