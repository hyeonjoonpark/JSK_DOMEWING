const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const products = [];
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;

        await signIn(page, username, password);
        await moveToPage(page, listURL);
        let list = await scrapeProducts(page);
        products.push(...list);

        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();


async function signIn(page, username, password) {
    await page.goto('https://pettob.co.kr/shop/main/intro_member.php?returnUrl=%2Fshop%2Fmain%2Findex.php', { waitUntil: 'networkidle0' });
    await page.type('#login_frm > div:nth-child(3) > input', username);
    await page.type('#login_frm > div:nth-child(4) > input', password);
    await page.click('#login_frm > div:nth-child(5) > a.btn.btn-default.btn-lg.submit.btn_login');
    await page.waitForNavigation();
}

async function moveToPage(page, listUrl) {
    let newUrl = '';
    if (listUrl.includes('&page=')) {
        const urlSplit = listUrl.split('&');
        newUrl = urlSplit[0] + '&page_num=' + 10000;
    }
    else newUrl = listUrl + '&page_num=' + 10000;

    await page.goto(newUrl, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#content > div.indiv > form:nth-child(1) > table > tbody > tr:nth-child(1) > td > ul li');
        for (const productElement of productElements) {
            const soldOutChecks = productElement.querySelector('dl > dd.sold');
            if (soldOutChecks) {
                continue;
            }
            const nameElement = productElement.querySelector('dl > dd.name').textContent.trim();
            const imageElement = productElement.querySelector('dl > dt > a > img');
            const priceElement = productElement.querySelector('dl > dd.price').textContent.trim();
            const hrefElement = productElement.querySelector('dl > dt > a');

            const name = nameElement ? removeExpiration(nameElement) : 'Name not found';
            const image = imageElement ? imageElement.src.trim() : 'Image URL not found';
            const href = hrefElement ? hrefElement.href.trim() : 'Detail page URL not found';
            const price = priceElement ? salePrice(priceElement).replace(/[^\d]/g, '') : 'Price not found';
            const platform = "펫투비";
            products.push({ name, price, image, href, platform });
        }
        return products;

        function removeExpiration(nameElement) {
            if (nameElement.includes(' - 유통기한')) {
                const name = nameElement.split(' - 유통기한')[0];
                return name;
            }
            return nameElement;
        }
        function salePrice(priceElement) {
            if (priceElement.includes('원 ')) {
                const price = priceElement.split('원 ')[1];
                return price;
            }
            return priceElement;
        }

    });
    return products;
}




