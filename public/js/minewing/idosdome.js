const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
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
    await page.goto('http://www.idosdome.com/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#b_white > font > b').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 1000;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    let newUrl = '';
    if (listUrl.includes('&page')) {
        const listUrlSplit = listUrl.split('&page');
        newUrl = listUrlSplit[0] + "&page=" + curPage + "&page_num=1000";
    }
    else newUrl = listUrl + "&page=" + curPage + "&page_num=1000";
    await page.goto(newUrl, { waitUntil: 'networkidle0' });
}


async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('body > table > tbody > tr:nth-child(2) > td > table > tbody > tr > td.outline_side > div.indiv > form:nth-child(1) > table:nth-child(8) > tbody > tr:nth-child(5) > td > table > tbody > tr > td');
        for (const productElement of productElements) {
            if (productElement.getAttribute('height') == '10') {
                continue;
            }
            const soldOutEl = productElement.querySelector('div:nth-child(1) > a > div:nth-child(2)');
            if (soldOutEl) {
                continue;
            }
            const nameElement = productElement.querySelector('div:nth-child(2) > a');
            const imageElement = productElement.querySelector('div:nth-child(1) > a > img');



            const priceElement = productElement.querySelector('div:nth-child(3) > b');
            const hrefElement = productElement.querySelector('div:nth-child(1) > a');


            const name = nameElement ? nameElement.textContent.trim().replace(/\[[^\]]*\]/g, "") : null;
            const image = imageElement ? imageElement.src.trim() : null;
            if (image.includes("/shop/data/skin/Horz/img/common/noimg_130.gif")) continue;
            const href = hrefElement ? hrefElement.href.trim() : null;
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : null;
            if (!name || !image || !href || !price) {
                continue;
            }
            const platform = "아이도스";
            products.push({ name, price, image, href, platform });
        }
        return products;

    });
    return products;
}





