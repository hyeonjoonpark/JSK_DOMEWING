const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const [listURL, memberId, password] = process.argv.slice(2);
        await signIn(page, memberId, password);

        // 로그인 후 전체 카테고리 페이지로 이동
        await moveToPage(page, `${listURL}`);

        let pageNum = 1;
        const allProducts = [];

        while (true) {
            const fullUrl = `${listURL}&page=${pageNum}`;
            console.log(fullUrl);
            await page.goto(fullUrl); // await 추가

            const products = await scrapeProducts(page);
            allProducts.push(...products); // 제품 추가

            console.log(`Page ${pageNum}: ${products.length} products found`);
            console.log(products);

            const hasNextPage = await checkHasNextPage(page, pageNum);
            if (!hasNextPage) {
                break;
            }

            pageNum += 1;
        }

        // 전체 제품 목록 출력
        console.log(`Total products: ${allProducts.length}`);
        console.log(allProducts);

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

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const products = [];
        const productElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpackage > div > ul > li');

        productElements.forEach(productElement => {
            const imageElement = productElement.querySelector('.thumb');
            const nameElement = productElement.querySelector('div.df-prl-desc > div > a > span');
            const priceElement = productElement.querySelector('div.df-prl-desc > div > ul > li.a-limited-price.df-prl-listitem-cell.summary_desc.xans-record- > span');

            const image = imageElement ? imageElement.src.trim() : '';
            const name = nameElement ? nameElement.textContent.trim() : '';
            const price = priceElement ? priceElement.textContent.trim() : '';

            if (name) {
                products.push({ image, name, price });
            }
        });
        return products;
    });

    return products;
}

async function checkHasNextPage(page, currentPageNum) {
    return await page.evaluate((currentPageNum) => {
        const paginationElements = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-normalpaging.ec-base-paginate > ol > li > a');
        const lastPageNum = parseInt(paginationElements[paginationElements.length - 1].textContent.trim(), 10);
        return currentPageNum < lastPageNum;
    }, currentPageNum);
}
