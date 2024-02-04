const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password, curPage] = args;
        await signIn(page, username, password);
        await moveToPage(page, listURL, curPage);
        await selectNumPages(page, listURL);
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://vitsonmro.com/mro/login.do', { waitUntil: 'networkidle2', timeout: 0 });
    await page.evaluate((username, password) => {
        document.querySelector('#custId').value = username;
        document.querySelector('#custPw').value = password;
        document.querySelector('#loginForm > div > a:nth-child(3)').click();
    }, username, password);
    await page.waitForNavigation();
}
async function selectNumPages(page, listURL) {
    await page.goto(listURL, { waitUntil: 'networkidle2', timeout: 0 });
    await page.select('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > span.k-pager-sizes.k-label > span > select', '60');
    await new Promise((page) => setTimeout(page, 5000));
}
async function moveToPage(page, listURL, curPage) {
    await page.goto(listURL, { waitUntil: 'networkidle2', timeout: 0 });
    curPage = parseInt(curPage);
    if (curPage != 1) {
        await page.evaluate((curPage) => {
            const pageBtn = document.querySelector('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > a:nth-child(4)');
            pageBtn.setAttribute('data-page', curPage);
            pageBtn.click();
        }, curPage);
        await new Promise((page) => setTimeout(page, 3000));
    }
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        function processProduct(productElement) {
            // Remove this.
            const index = parseInt(productElement.querySelector('td:nth-child(3)').textContent.trim());
            //
            const stockText = productElement.querySelector('td:nth-child(9) > span.hdsp_bot').textContent.trim();
            if (stockText !== '재고보유') {
                return false;
            }
            const productName = productElement.querySelector('td:nth-child(6) > span.hdsp_top.link > a').textContent.trim();
            const standard = productElement.querySelector('td:nth-child(6) > span.hdsp_bot').textContent.trim();
            const name = productName + ' ' + standard;
            const productPriceText = productElement.querySelector('td:nth-child(10) > span.hdsp_top.price_cr').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const image = productElement.querySelector('td:nth-child(4) > div > img').getAttribute('src');
            const productCode = productElement.querySelector('td:nth-child(5) > span.hdsp_top').textContent.replace(/[^0-9]/g, '').trim();
            const href = 'https://vitsonmro.com/mro/shop/productDetail.do?productCode=' + productCode;
            const platform = '비츠온엠알오';
            return { name, price, image, href, platform, index };
        }
        const productElements = document.querySelectorAll('#grid > div.k-grid-content.k-auto-scrollable > table > tbody tr');
        const products = [];
        productElements.forEach(productElement => {
            const result = processProduct(productElement);
            if (result !== false) {
                products.push(result);
            }
        });
        return products;
    });
    return products;
}