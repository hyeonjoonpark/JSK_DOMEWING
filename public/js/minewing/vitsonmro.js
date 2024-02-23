const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        const signInResult = await signIn(page, username, password);
        if (signInResult === false) {
            console.log(false);
            return;
        }
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            const moveToPageResult = await moveToPage(page, i);
            if (moveToPageResult === false) {
                await moveToPage();
            }
            let list = await scrapeProducts(page);
            products.push(...list);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function getNumPage(page, url) {
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 0 });
    await page.evaluate(() => {
        const isPopup = document.querySelector('#groobeeWrap');
        if (isPopup) {
            isPopup.style.display = 'none';
            document.querySelector('body > div.grbDim.grbLayer').style.display = 'none';
        }
    });
    await page.select('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > span.k-pager-sizes.k-label > span > select', '60');
    await new Promise((page) => setTimeout(page, 3000));
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('body > div.container > div > div.content > div.top_toolbar.align_side > div.tool_left > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const numPerPage = 60;
    const numPage = Math.ceil(numProducts / numPerPage);
    return numPage;
}
async function signIn(page, username, password) {
    await page.goto('https://vitsonmro.com/mro/login.do', { waitUntil: 'networkidle0' });
    await page.type('#custId', username);
    await page.type('#custPw', password);
    await page.click('#loginForm > div > a:nth-child(3)');
    await page.waitForSelector('#wrap');
    return true;
}
async function moveToPage(page, curPage) {
    curPage = parseInt(curPage);
    const selector = `a[data-page="${curPage}"]`; // 동적 셀렉터 생성
    const result = await page.evaluate(selector => {
        const link = document.querySelector(selector);
        if (link) {
            link.click(); // 링크가 존재하면 클릭
            return true;
        } else {
            return false;
        }
    }, selector);
    await new Promise((page) => setTimeout(page, 3000));
    return result;
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        function processProduct(productElement) {
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
            if (image.includes('이미지준비중')) {
                return false;
            }
            const productCode = productElement.querySelector('td:nth-child(5) > span.hdsp_top').textContent.replace(/[^0-9]/g, '').trim();
            const href = 'https://vitsonmro.com/mro/shop/productDetail.do?productCode=' + productCode;
            const platform = '비츠온엠알오';
            return { name, price, image, href, platform };
        }
        const productElements = document.querySelectorAll('#grid > div.k-grid-content.k-auto-scrollable > table > tbody tr');
        const products = [];
        for (const productElement of productElements) {
            const result = processProduct(productElement);
            if (result !== false) {
                products.push(result);
            }
        }
        return products;
    });
    return products;
}