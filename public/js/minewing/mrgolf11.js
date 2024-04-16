const getForbiddenWords = require('../forbidden_words');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password);
        await moveToPage(page, listURL);
        const numPage = await getNumPage(page, listURL);
        const forbiddenWords = getForbiddenWords();
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, listURL, i);
            let list = await scrapeProducts(page, forbiddenWords);
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
    await page.goto('https://mrgolf11.cafe24.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > fieldset > div.login__button > a.btnSubmit.gFull.sizeL');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numProducts = await page.evaluate(() => {
        const numProductsElement = document.querySelector('strong.txtEm');
        if (!numProductsElement) {
            console.error('No product count element found.');
            return 0; // 적절한 기본값 또는 에러 처리 로직
        }
        const numProductsText = numProductsElement.textContent.trim();
        return parseInt(numProductsText.replace(/[^\d]/g, ''), 10);
    });
    const countProductInPage = 20;
    const numPage = Math.ceil(numProducts / countProductInPage);
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    const url = new URL(listUrl);
    url.searchParams.set('page', curPage);

    await page.goto(url.toString(), { waitUntil: 'domcontentloaded' });
}
async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('div.normalpackage_box.section > div > div:nth-child(2) > div.xans-element-.xans-product.xans-product-listnormal.ec-base-product > ul.prdList.grid4 > li div.prdList__item');
        const products = [];
        for (const productElement of productElements) {
            const product = scrapeProduct(productElement, forbiddenWords);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        return products;
        function scrapeProduct(productElement, forbiddenWords) {
            try {
                const soldOutImageElement = productElement.querySelector('img[src="/web/upload/custom_4.gif"]');
                if (soldOutImageElement) {
                    return false; // 판매 완료된 상품 건너뛰기
                }
                const name = productElement.querySelector('div > div.description > div.name > a > span:nth-child(2)').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false; // 금지된 단어가 포함된 상품 건너뛰기
                    }
                }
                const priceElement = productElement.querySelector('div > div.description > ul > li > span:nth-child(2)');
                if (!priceElement) {
                    return false; // 가격 정보가 없는 상품 건너뛰기
                }
                const priceText = priceElement.textContent.trim().replace(/[^\d]/g, '');
                if (priceText === '') {
                    return false; // 가격 정보가 비어있는 경우
                }
                const price = parseInt(priceText);
                if (price < 1) {
                    return false; // 가격이 0원 이하인 상품 건너뛰기
                }
                const image = productElement.querySelector('div > div.thumbnail > a > img').src;
                const href = productElement.querySelector('div > div.thumbnail > a').href;
                const platform = "미스터골프";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false; // 에러 발생 시 상품 건너뛰기
            }
        }
    }, forbiddenWords);
    return products;
}
