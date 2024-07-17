const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName } = require('./common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listUrl, username, password] = process.argv.slice(2);

    await page.setDefaultNavigationTimeout(0);

    try {
        await signIn(page, username, password, 'https://www.jsweet.co.kr/member/login.html', '#member_id', '#member_passwd', 'div > fieldset > a.btnSubmit.sizeL.df-lang-button-login');
        await goToAttempts(page, listUrl, 'domcontentloaded');
        const lastPageNumber = await getLastPageNumber(page);
        const products = [];
        for (let i = lastPageNumber; i > 0; i--) {
            await goToAttempts(page, listUrl + '&page=' + i, 'domcontentloaded');
            const listProducts = await getListProducts(page);
            for (const product of listProducts) {
                const isValidImage = await checkImageUrl(product.image);
                const isValidProduct = await checkProductName(product.name);
                if (isValidImage && isValidProduct) {
                    product.name = await formatProductName(product.name);
                    products.push(product);
                }
            }
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
            const isSoldOut = Array.from(pe.querySelectorAll('div > div.df-prl-desc > div > div.df-prl-icon > img.icon_img')).some(img => {
                return img.src.includes('product_soldout.png') && img.alt.includes('품절');
            });
            if (isSoldOut) {
                return false;
            }

            const nameElement = pe.querySelector('div > div.df-prl-desc > div > a > span');
            if (!nameElement) {
                return false;
            }

            const priceElements = pe.querySelectorAll('div > div.df-prl-desc > div > ul > li.df-prl-listitem-cell.product_price.xans-record- > span:nth-child(2)');
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
            const href = 'https://www.jsweet.co.kr/' + hrefElement.getAttribute('href');
            const platform = '제이스윗';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
