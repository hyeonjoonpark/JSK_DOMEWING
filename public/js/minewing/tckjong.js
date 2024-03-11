const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        // const listURL = 'https://www.tckjong.com/shop/big_section.php?cno1=1051';
        // const username = 'jskorea2023';
        // const password = 'Tjddlf88!@';

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
    await page.goto('https://www.tckjong.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#login_id', username);
    await page.type('#login_pwd', password);
    await page.click('#login > div.inner > div.login_form > form > span > input[type=submit]');
    await page.waitForNavigation();
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#big_section > div.list_section > div > div > p > span').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 30;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}


async function moveToPage(page, listUrl, curPage) {
    curPage = parseInt(curPage);
    const listUrlSplit = listUrl.split('?');
    const newUrl = listUrlSplit[0] + '?page=' + curPage + '&' + listUrlSplit[1];
    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#big_section > div.list_section > div > table > tbody > tr > td');
        for (const productElement of productElements) {
            const skipChecks = document.querySelectorAll('div > div.wrap_img > div.icon img');
            if (skipChecks) {
                if (checkSkipProduct(skipChecks)) {
                    continue;
                }
            }
            if (productElement.classList.contains('empty_cell')) {
                continue;
            }
            const nameElement = productElement.querySelector('div > p.name > a');
            const imageElement = productElement.querySelector('div > div.wrap_img > div.img > a > img');
            const priceElement = productElement.querySelector('div > div.prc > span > strong');
            const hrefElement = productElement.querySelector('div > p.name > a');

            const name = nameElement ? nameElement.textContent.trim() : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : 'Price not found';
            const platform = "특종몰";
            products.push({ name, price, image, href, platform });
        }
        return products;
        function checkSkipProduct(imageElements) {
            const skipImage = 'https://tckjongg.wisacdn.com/_data/icon/160501c68402aa61c5b2bc81c64086d2.gif';
            for (const imageElement of imageElements) {
                const image = imageElement.src;
                if (image == skipImage) {
                    return true;
                }
            }
        }
    });
    return products;
}




