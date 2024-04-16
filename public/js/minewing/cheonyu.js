const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, listURL, i);
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


async function signIn(page, username, password) {
    await page.goto('https://www.cheonyu.com/member/login.html?url=%2F', { waitUntil: 'networkidle0' });
    await page.type('#inMID', username);
    await page.type('#inMPW', password);
    await page.click('#frmLogin > input.newloginbtn.ptsans');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#ProductCount').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 100;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    const urlObj = new URL(listUrl);
    const cateIDX = urlObj.searchParams.get("cateIDX");

    const listUrlSplit = listUrl.split('?');
    const subUrl = '&viewType=1&listSize=100&s1=&s2=1&s3=&s4=&s5=&searchKind=&searchDate=';
    let newUrl = listUrlSplit[0] + '?page=' + curPage + '&cateIDX=' + cateIDX + subUrl;
    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#sub_list > div.m_list > ul li');
        for (const productElement of productElements) {

            const promotionElement = productElement.querySelector('div.soldout_bg');
            if (promotionElement) {
                continue;
            }



            const nameElement = productElement.querySelector('div > div.m_pdt_list_name').textContent.trim();
            const imageElement = productElement.querySelector('div > a > img').src.trim();
            const priceElement = productElement.querySelector('div > div.m_pdt_list_price > div.price_wrap > span.price');
            const hrefElement = productElement.querySelector('div > a');

            const readyImage = "/_DATA/noimage.gif";
            if (imageElement.includes(readyImage)) {
                continue;
            }

            const name = nameElement ? nameElement.replace(/\[[^\]]*\]/g, "") : null;
            const image = imageElement ? imageElement : null;
            const href = hrefElement ? hrefElement.href.trim() : null;
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : null;
            if (!name || !image || !href || !price) {
                continue;
            }
            const platform = "천유닷컴";
            products.push({ name, price, image, href, platform });
        }
        return products;

    });
    return products;
}





