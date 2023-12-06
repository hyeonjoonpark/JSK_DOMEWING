const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password, keyword] = args;
        await page.goto('https://vitsonmro.com/mro/login.do');
        await page.waitForSelector('#custId');
        await page.waitForSelector('#custPw');
        await page.waitForSelector('#loginForm > div > a:nth-child(3)');
        await page.type('#custId', username);
        await page.type('#custPw', password);
        await page.click('#loginForm > div > a:nth-child(3)');
        await page.waitForSelector('#keyword');
        await page.waitForSelector('#searchForm > button');
        await page.type('#keyword', keyword);
        await page.click('#searchForm > button');
        await page.waitForSelector('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > span.k-pager-sizes.k-label > span > select');
        await page.select('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > span.k-pager-sizes.k-label > span > select', '60');
        await page.waitForSelector('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > div > ul > li:nth-child(1) > span');
        const numPage = await page.evaluate(() => {
            const pageElements = document.querySelectorAll('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > div > ul > li');
            return pageElements.length;
        });
        console.log(numPage);
        const products = [];
        for (let i = 1; i <= numPage; i++) {
            if (i != 1) {
                await page.waitForSelector('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > div > ul > li:nth-child(' + i + ') > a');
                await page.click('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > div > ul > li:nth-child(' + i + ') > a');
            }
            await page.waitForSelector('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > span.k-pager-info.k-label');
            const productsArr = await page.evaluate(() => {
                const productElements = document.querySelectorAll('#grid > div.k-grid-content.k-auto-scrollable > table > tbody > tr');
                const productsArr = [];
                for (const productElement of productElements) {
                    const name = productElement.querySelector('td:nth-child(6) > span.hdsp_top.link > a').textContent;
                    const price = productElement.querySelector('td:nth-child(10) > span.hdsp_top.price_cr').textContent;
                    const productCode = productElement.querySelector('td:nth-child(5) > span.hdsp_top').textContent;
                    const href = 'https://vitsonmro.com/mro/shop/productDetail.do?productCode=' + productCode;
                    const image = productElement.querySelector('td:nth-child(4) > div > img').getAttribute('src');
                    const platform = '비츠온엠알오';
                    const product = {
                        name: name,
                        price: price,
                        href: href,
                        image: image,
                        platform: platform
                    };
                    productsArr.push(product);
                }
                return productsArr;
            });
            products.push(...productsArr);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();