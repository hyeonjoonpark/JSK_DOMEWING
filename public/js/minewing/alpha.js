const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);

    try {
        await login(page, username, password);
        await processPage(page, listURL);
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://www.alpha.co.kr/member/login/login.do?', { waitUntil: 'networkidle0' });
    await page.type('#M_USERID', username);
    await page.type('#M_PASSWD', password);
    await page.click('#dataForm > div > fieldset > div.action > a');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#productSort > div.pull-left > span').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    listURL += '&pageSize=' + numProducts;
    await page.goto(listURL, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('#tab111 > div dl');
        const products = [];

        function processProduct(productElement) {
            const productNameElement = productElement.querySelector('#tab111 > div> dl > dd.pName > a');
            const name = productNameElement.textContent.trim();
            const productPriceText = productElement.querySelector('#tab111 > div > dl > dd.price.type2').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const imageElement = productElement.querySelector('#tab111 > div > dl > dd.imgArea > a > img');
            const image = imageElement.src;
            const href = productElement.querySelector('#tab111 > div > dl > dd.imgArea > a').href.trim();
            const platform = '도매포유';

            return { name, price, image, href, platform };
        }
        function hasStockByText(productElement) {
            // 상품 요소 내의 모든 텍스트 노드를 검사하여 '품절' 텍스트가 있는지 확인
            const textContent = productElement.textContent || productElement.innerText;
            return !textContent.includes('품절');
        }

        for (const productElement of productElements) {
            if (hasStockByText(productElement)) {
                try {
                    const productInfo = processProduct(productElement);
                    products.push(productInfo);
                } catch (error) {
                    console.error("Error processing product: ", error);
                }
            }
        }

        return products;
    });

    return products;
}

