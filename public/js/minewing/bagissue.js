const getForbiddenWords = require('../forbidden_words');
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);
    try {
        await signIn(page, username, password);
        await scrapeAllPages(page, listURL);
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function signIn(page, username, password) {
    await page.goto('https://bagissue.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > div > div.login > fieldset > span.login_btn');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function scrapeAllPages(page, listURL) {
    const forbiddenWords = getForbiddenWords();
    let products = [];
    for (let pageNum = 20; pageNum > 0; pageNum--) {
        await moveToPage(page, listURL, pageNum);
        const pageProducts = await scrapeProducts(page, forbiddenWords);
        products = products.concat(pageProducts);
    }
    console.log(JSON.stringify(products));
}

async function moveToPage(page, listURL, pageNum) {
    const url = `${listURL}&page=${pageNum}`;
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}

async function scrapeProducts(page, forbiddenWords) {
    const products = await page.evaluate((forbiddenWords) => {
        const productElements = document.querySelectorAll('ul.prdList.column_3 > li');
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
                const soldOutImageElement = productElement.querySelector('img[src="/shop/data/skin/everybag/img/icon/good_icon_soldout.gif"]');
                if (soldOutImageElement) {
                    return false;
                }
                let name = productElement.querySelector('div > span > div.prdImg_box > div.item_name.left > a > span').textContent.trim();
                for (const forbiddenWord of forbiddenWords) {
                    if (name.includes(forbiddenWord)) {
                        return false;
                    }
                }
                name = name.replace(/[a-zA-Z]\d{3}/g, ''); // 영어 1개 숫자 3개 패턴 제거
                name = name + " 패션 데일리 캐쥬얼 여성가방"; // 문자열 추가

                const price = parseInt(productElement.querySelector('div.prdImg_box > ul > li > span:nth-child(2)').textContent.trim().replace(/[^\d]/g, ''));
                if (price < 1) {
                    return false;
                }
                const image = productElement.querySelector('div.prdImg_box > div.prdImg_image > a > img').src;
                const href = productElement.querySelector('div.prdImg_box > div.prdImg_image > a').href;
                const platform = "벡이슈";
                const product = { name, price, image, href, platform };
                return product;
            } catch (error) {
                return false;
            }
        }
    }, forbiddenWords);
    return products;
}
