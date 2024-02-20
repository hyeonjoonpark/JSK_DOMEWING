const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        let curPage = args[3];
        await signIn(page, username, password);
        // await moveToList(page, listURL);
        curPage = parseInt(curPage, 10);
        if (curPage > 1) {
            await moveToPage(page, curPage);
        }
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://candle-box.com/member/login.html?noMemberOrder&returnUrl=%2Fmyshop%2Forder%2Flist.html', { waitUntil: 'networkidle2', timeout: 0 });
    await page.evaluate((username, password) => {
        document.querySelector('#member_id').value = username;
        document.querySelector('#member_passwd').value = password;
        document.querySelector('#loginForm > div > a:nth-child(3)').click();
    }, username, password);
    await page.waitForNavigation({ timeout: 0 });
}//로그인까지 메소드 완료

// async function moveToList(page, listURL) {
//     await page.goto(listURL, { waitUntil: 'networkidle2', timeout: 0 });
//     await new Promise((page) => setTimeout(page, 10000));
// }

async function moveToPage(page, curPage) {
    curPage = parseInt(curPage);
    await page.evaluate((curPage) => {//질문사항 비츠엠알오를 보면 2번쨰 페이지 버튼을 누르는데 다음 버튼을 누르지 않고 2번째 버튼을 눌렀었는지
        const pageBtn = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpaging.ec-base-paginate > a:nth-child(4) > img');
        pageBtn.setAttribute('data-page', curPage);
        pageBtn.click();
    }, curPage);
    await new Promise((page) => setTimeout(page, 5000));
}//다음페이지 이동 구현 완료
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        function processProduct(productElement) {
            const product=[];
            const stockText = productElement.querySelector('[id^="anchorBoxId_"] > div.description > div > img').getAttribute('alt');
            if (stockText !== '품절') {
                return false;
            }
            const name = productElement.querySelector('div.description > strong > a > span:nth-child(2)').textContent.trim();
            const productPriceText = productElement.querySelector('div.description > ul > li:nth-child(1) > span:nth-child(2)').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const image = productElement.querySelector('[id^="eListPrdImage"]').getAttribute('src');
            const urlBase = 'https://candle-box.com';
            const subHref = productElement.querySelector('div.description > strong > a').getAttribute('href');
            const href = urlBase + subHref;
            const platform = '캔들아트';
            product.push({name, price, image, href, platform})
            return product;
        }
        const productElements = document.querySelectorAll('[id^="eListPrdImage"] > div.description > strong > a > span:nth-child(2)');
        for (const productElement of productElements) {
            const result = processProduct(productElement);
            if (result !== false) {
                products.push(result);
            }
        }

    });
    return products;
}
