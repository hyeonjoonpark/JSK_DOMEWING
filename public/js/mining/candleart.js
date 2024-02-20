const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password, curPage] = args;
        await signIn(page, username, password);
        await processUrl(page, listURL, curPage);
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function processUrl(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    const processedUrl = listUrl.split('?')[0] + '?page=' + curPage;
    await page.goto(processedUrl, { waitUntil: 'networkidle2' });
}
async function signIn(page, username, password) {
    await page.goto('https://candle-box.com/member/login.html', { waitUntil: 'networkidle2' });
    await page.evaluate((username, password) => {
        document.querySelector('#member_id').value = username;
        document.querySelector('#member_passwd').value = password;
    }, username, password);
    await page.click('a[class="btnLogin"]');
    await page.waitForNavigation();
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div.xans-element-.xans-product.xans-product-listnormal.ec-base-product > ul > li');
        for (const productElement of productElements) {
            const nameElement = productElement.querySelector('.description .name a');
            const imageElement = productElement.querySelector('.thumbnail .prdImg img');
            const priceElement = productElement.querySelector('.description .spec li:first-child');
            if (!nameElement) {
                continue;
            }
            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = nameElement ? nameElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.split(':')[1].trim() : 'Price not found';
            const platform = "캔들아트";
            products.push({ name, price, image, href, platform });
        }
        return products;
    });
    return products;
}
