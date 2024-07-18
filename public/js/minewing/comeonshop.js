const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl, checkProductName, trimProductCodes } = require('./common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewport({ width: 1500, height: 1000 });
    const [listUrl, username, password] = process.argv.slice(2);
    await page.setDefaultNavigationTimeout(0);

    try {
        await signIn(page, username, password, 'https://comeonshop.co.kr/member/login.html', '#member_id', '#member_passwd', '.user-login > fieldset > a');
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
                    product.name = product.name.replace(/\(.*?\)/g, '').trim(); // 정규식을 사용하여 괄호 안의 문자열 제거
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
        const productElements = document.querySelectorAll('li.item.DB_rate.xans-record-');
        const products = [];
        for (const pe of productElements) {
            const isSoldOut = Array.from(pe.querySelectorAll('div.description img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
            if (isSoldOut) {
                continue;
            }
            const nameElement = pe.querySelector('a > div.add_thumb > img');
            if (!nameElement) {
                continue;
            }
            const priceElement = pe.getAttribute('data-price');
            if (!priceElement) {
                continue;
            }
            const price = parseInt(priceElement.replace(/[^0-9]/g, '').trim());
            if (!price) {
                continue;
            }
            const imageElement = pe.querySelector('a > div.add_thumb > img');
            if (!imageElement) {
                continue;
            }

            const hrefElement = pe.querySelector('div.thumbnail > a');
            if (!hrefElement) {
                continue;
            }

            let name = imageElement.getAttribute('alt').trim();
            name = name.replace(/\(.*?\)/g, '').trim(); // 정규식을 사용하여 괄호 안의 문자열 제거
            const image = imageElement.src;
            let href = 'https://comeonshop.co.kr/' + hrefElement.getAttribute('href');
            const platform = '커먼샵';
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}
