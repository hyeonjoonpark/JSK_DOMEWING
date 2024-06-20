const getForbiddenWords = require('../forbidden_words');
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
    await page.goto('https://sapakorea.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'domcontentloaded' });
    const numPage = await page.evaluate(() => {
        const lastPageLink = document.querySelector('a.last');
        if (!lastPageLink) {
            return false;
        }
        const hrefValue = lastPageLink.getAttribute('href');
        const pageNumberMatch = hrefValue.match(/page=(\d+)/);
        if (!pageNumberMatch) {
            return false;
        }
        return parseInt(pageNumberMatch[1], 10);
    });
    return numPage;
}

async function moveToPage(page, listUrl, curPage) {
    const url = new URL(listUrl);
    url.searchParams.set('page', curPage);
    await page.goto(url.toString(), { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('li.item.xans-record-');
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
                const soldOutImageElement = productElement.querySelector('img[src*="ico_product_soldout.gif"]');
                if (soldOutImageElement) {
                    return false; // 판매 완료된 상품 건너뛰기
                }
                const name = productElement.querySelector('div > p > strong > a > span:nth-child(2)').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false; // 금지된 단어가 포함된 상품 건너뛰기
                    }
                }
                const priceElement = productElement.querySelector('div > ul > li:nth-child(2) > span:nth-child(2)');
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
                const image = productElement.querySelector('div > a > img.thumb').src;
                const href = productElement.querySelector('div.box > a').href;
                const platform = "싸파코리아";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false; // 에러 발생 시 상품 건너뛰기
            }
        }
    }, forbiddenWords);
    return products;
}
