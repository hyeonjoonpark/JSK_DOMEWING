const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const keyword = process.argv[2];
        await page.goto('https://domeggook.com/ssl/member/mem_loginForm.php');
        await page.waitForSelector('#idInput');
        await page.waitForSelector('#pwInput');
        await page.type('#idInput', 'sungil2018');
        await page.type('#pwInput', 'tjddlf88!@');
        await page.waitForSelector('input[type="submit"]');
        await page.click('input[type="submit"]');
        await page.waitForNavigation();
        await page.waitForSelector('#searchWordForm');
        await page.type('#searchWordForm', keyword);
        await page.click('#searchWordSubmit');
        await page.waitForNavigation();
        await page.waitForSelector('#lSz');
        await page.click('#lSz');
        await page.waitForSelector('#lSz > ol > li:nth-child(3)');
        await page.click('#lSz > ol > li:nth-child(3)');
        await wait(2);
        await page.waitForSelector('#lLst > ol');

        // 상품 정보 추출
        const products = await page.evaluate(() => {
            const productElements = document.querySelectorAll('#lLst > ol > li');
            const productsArr = [];

            for (const productElement of productElements) {
                const nameText = productElement.querySelector('li > div.main > a').textContent;
                const name = nameText.replace(/\s+/g, '');
                const priceText = productElement.querySelector('li > div.amtqty > div.amt > b').textContent;
                const priceNumber = priceText.match(/\d+/g);
                const price = parseInt(priceNumber.join(''), 10);
                const imageText = productElement.querySelector('li > a > img');
                const image = imageText.getAttribute('src');
                const hrefSelector = productElement.querySelector('li > a').getAttribute('href');
                const href = 'http://domeggook.com/' + hrefSelector;
                const platform = '도매매';
                productsArr.push({ name, price, image, href, platform });
            }

            return productsArr;
        });

        // 상품 정보 출력
        console.log(JSON.stringify(products));
    } catch (error) {
        console.log(JSON.stringify(error));
    } finally {
        await browser.close();
    }
})();
async function wait(seconds) {
    return new Promise(resolve => setTimeout(resolve, seconds * 1000));
}