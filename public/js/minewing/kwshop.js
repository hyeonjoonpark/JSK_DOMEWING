const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
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
    await page.goto('https://kwshop.co.kr/member/login', { waitUntil: 'networkidle0' });
    await page.type('#userid', username);
    await page.type('#password', password);
    await page.click('#layout_config_full > div.login_top_conbx.mt20 > div > div > form > div > table > tbody > tr:nth-child(1) > td:nth-child(4) > input[type=image]');
    await page.waitForNavigation();
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#layout_config_full > p > b').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const countProductInPage = 16; // 페이지당 상품 수
    const numPage = Math.ceil(numProducts / countProductInPage); // 전체 페이지 수 계산
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    const url = new URL(listUrl);
    url.searchParams.set('page', curPage); // 페이지 번호를 현재 페이지로 설정

    await page.goto(url.toString(), { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('div.displayTabContentsContainer.displayTabContentsList form');

        for (const productElement of productElements) {
            const nameElement = productElement.querySelector('div.goodsDisplayInformation_in > ul > li > a > span');
            const imageElement = productElement.querySelector('li.goodsDisplayImgcon > div > div > a > img');
            const priceElement = productElement.querySelector('div.gd_opt_c.gd_opt_name > div:nth-child(2) > div > dl > dd > span.sale_price');
            const hrefElement = productElement.querySelector('ul.goodsDisplayItemWrap > li.goodsDisplayImgcon > div > div > a');

            if (nameElement && imageElement && priceElement && hrefElement) {
                const rawName = nameElement.textContent.trim();
                const name = rawName.replace("강원전자", "").trim(); // "강원전자" 문자열 제거
                const image = imageElement.src.trim();
                const href = hrefElement.href.trim();
                const priceText = priceElement.textContent.trim().replace(/[^\d]/g, '');
                const price = parseInt(priceText, 10); // 가격을 정수로 변환

                if (price >= 1) { // 가격이 1원 이상인 경우에만 상품을 추가
                    const platform = "강원전자";
                    products.push({ name, price, image, href, platform });
                }
            }
        }
        return products;
    });
    return products;
}




