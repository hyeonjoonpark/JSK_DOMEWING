const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName, trimProductCodes } = require('./common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewport({ width: 1500, height: 1000 });
    const [listUrl, username, password] = process.argv.slice(2);
    await page.setDefaultNavigationTimeout(0);

    try {
        await signIn(page, username, password, 'https://bagissue.kr/member/login.html', '#member_id', '#member_passwd', 'span.login_btn');
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
        const lastPageUrl = document.querySelector('ol > li.xans-record-:last-child > a').getAttribute('href');
        const urlParams = new URLSearchParams(lastPageUrl);
        const pageValue = urlParams.get('page');
        return pageValue;
    });
    return lastPageNumber ? parseInt(lastPageNumber) : 1;
}

async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('li.item.xans-record-');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const nameElement = pe.querySelector('div.prdImg_box > div.item_name.left > a > span');
            if (!nameElement) {
                return false;
            }
            const priceElements = pe.querySelectorAll('div.prdImg_box li > span:nth-child(2)')
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }
            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }
            const imageElement = pe.querySelector('.prdImg_image > a > img')
            if (!imageElement) {
                return false;
            }

            const hrefElement = pe.querySelector('.prdImg_image > a');
            if (!hrefElement) {
                return false;
            }

            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            let href = 'https://bagissue.kr/' + hrefElement.getAttribute('href');
            const platform = '벡이슈';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
