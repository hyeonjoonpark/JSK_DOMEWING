const puppeteer = require('puppeteer');
async function getProductList(page) {
    return page.evaluate(() => {
        const productSelector = 'td[valign="top"]';
        const baseURL = 'https://dometopia.com';
        const products = [];

        const getProductDetails = productElement => {
            try {
                const nameSelector = 'dl > dd.goodsDisplayTitle > div > a > h6';
                const priceSelector = 'dl > dd.goodsDisplaySalePrice.clfix > div > table > tbody > tr > td.price_num';
                const imageSelector = 'dl > dt > span > a > img';
                const hrefSelector = 'dl > dd.goodsDisplayTitle > div > a';

                const name = productElement.querySelector(nameSelector).textContent.trim();
                const productPriceText = productElement.querySelector(priceSelector).textContent;
                const image = productElement.querySelector(imageSelector).getAttribute('src');
                const productHref = productElement.querySelector(hrefSelector).getAttribute('href');

                if (!name.includes('해외직구')) {
                    const price = parseInt(productPriceText.replace(/[^0-9]/g, '').trim());
                    const href = baseURL + productHref;
                    const platform = '도매토피아';

                    return { name, price, image, href, platform };
                }
            } catch (error) {
                console.error('Error extracting product details:', error);
            }
        };

        document.querySelectorAll(productSelector).forEach(productElement => {
            const productDetails = getProductDetails(productElement);
            if (productDetails) {
                products.push(productDetails);
            }
        });

        return products;
    });
}
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password, curPage] = args;
        // Sign-in.
        await page.goto('https://dometopia.com/member/login', { waitUntil: 'networkidle2', timeout: 0 });
        await page.type('#userid', username);
        await page.type('#password', password);
        await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
        await page.waitForNavigation();
        // Generate the full URL.
        const params = '&perpage=150&page=' + curPage;
        const fullURL = listURL + params;
        // Move to the URL page.
        await page.goto(fullURL, { waitUntil: 'networkidle2', timeout: 0 });
        // Get the products in the list.
        const products = await getProductList(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
