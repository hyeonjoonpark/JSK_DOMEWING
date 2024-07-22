const puppeteer = require('puppeteer');
const { goToAttempts, signIn, checkImageUrl, checkProductName, formatProductName } = require('./common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listUrl, username, password] = process.argv.slice(2);

    await page.setDefaultNavigationTimeout(0);

    try {
        await signIn(page, username, password, 'https://www.realbag.kr/member/login.html', '#member_id', '#member_passwd', 'div.loginform > fieldset > a > img');
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
        const lastPageUrl = document.querySelector('p.last > a').getAttribute('href');
        const urlParams = new URLSearchParams(lastPageUrl);
        const pageValue = urlParams.get('page');
        return pageValue;
    });
    return lastPageNumber ? parseInt(lastPageNumber) : 1;
}
async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('[id^="anchorBoxId_"]');
        const products = [];
        for (const pe of productElements) {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        }
        function buildProduct(pe) {
            const isSoldOutElement = pe.querySelector('div > p > a > span > font');
            if (isSoldOutElement && isSoldOutElement.textContent.includes('품절')) {
                return false;
            }

            const nameElement = pe.querySelector('div > p > a > span');
            if (!nameElement) {
                return false;
            }

            const fontTag = nameElement.querySelector('font');
            if (fontTag) {
                fontTag.remove();
            }

            // Check if nameElement contains the word "품절"
            if (nameElement.textContent.includes('품절')) {
                return false;
            }

            // Get the text content up to the first <br> tag
            let name = nameElement.innerHTML.split('<br>')[0].trim();
            name = name.replace(/<\/?[^>]+(>|$)/g, ""); // Remove any remaining HTML tags

            // If name is 5 characters or less, add "데일리 패션 가방"
            if (name.length <= 5) {
                name += " 데일리 패션";
            }

            if (!name) {
                return false;
            }

            const priceElements = pe.querySelectorAll('span[style="font-size:12px;color:#000000;font-weight:bold;"]');
            let priceText = '';
            for (const priceElement of priceElements) {
                priceText += priceElement.textContent.trim();
            }

            const price = parseInt(priceText.replace(/[^0-9]/g, '').trim());
            if (!price) {
                return false;
            }

            const imageElement = pe.querySelector('div > a > img')
            if (!imageElement) {
                return false;
            }
            const hrefElement = pe.querySelector('div > a');
            if (!hrefElement) {
                return false;
            }
            const image = imageElement.src;
            const href = 'https://www.realbag.kr/' + hrefElement.getAttribute('href');
            const platform = '리얼백';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
