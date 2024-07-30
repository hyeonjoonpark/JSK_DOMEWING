const puppeteer = require('puppeteer');

/**
 * https://joowb.com/
 */

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const [listURL, memberId, password] = process.argv.slice(2);
        await signIn(page, memberId, password);

        // 로그인 후 전체 카테고리 페이지로 이동
        await moveToPage(page, `${listURL}/product/list.html`);

        // 카테고리 페이지에서 카테고리 번호를 추출
        const categories = await page.evaluate(allCategoriesNumber);
        console.log(categories);

        let allProducts = [];

        console.log(`categories.length = ${categories.length}`);

        for (const category of categories) {
            let pageNum = 1;
            while (true) {
                await moveToPage(page, `${listURL}/product/list.html?cate_no=${category}&page=${pageNum}`);
                const products = await scrapeProducts(page);

                if (products.length === 0) break;
                console.log(`Category: ${category}, Page: ${pageNum}`);
                console.log(`Products:`, products);
                allProducts = allProducts.concat(products);
                console.log(`products.length = ${products.length}`);

                pageNum++;
            }
        }

        console.log("All Products:", allProducts);
        console.log(`all products.length = ${allProducts.length}`);

    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function signIn(page, memberId, password) {
    await page.goto('https://joowb.com/member/login.html', { waitUntil: 'load' });

    // 로그인 폼이 로드될 때까지 기다림
    await page.waitForSelector('#member_id');
    await page.type('#member_id', memberId);

    await page.waitForSelector('#member_passwd');
    await page.type('#member_passwd', password);

    // 로그인 버튼의 선택자가 올바른지 확인하고 클릭
    const loginButtonSelector = '.btnSubmit.sizeL.df-lang-button-login';
    await page.waitForSelector(loginButtonSelector);
    await page.click(loginButtonSelector);

    // 로그인 후 페이지 로드가 완료될 때까지 기다림
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
}

async function moveToPage(page, url) {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
}

async function allCategoriesNumber() {
    const liElements = document.querySelectorAll('ul.xans-element- li[df-cate-no]');
    const cateNos = Array.from(liElements).map(li => li.getAttribute('df-cate-no'));
    return cateNos;
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('.df-prl-fadearea');
        
        productElements.forEach(productElement => {
            const imageElement = productElement.querySelector('#anchorBoxId_658 > div > div.df-prl-thumb');
            
            const name = productElement.querySelector('#anchorBoxId_660 > div > div.df-prl-desc > div > a > span')?.textContent.trim() || '';
            const price = productElement.querySelector('ul > li.a-limited-price.df-prl-listitem-cell.product_price.xans-record- > span:nth-child(2)')?.textContent.trim() || '';

            if (name) {
                products.push({ imageElement, name, price });
            }
        });
        return products;
    });

    return products;
}

