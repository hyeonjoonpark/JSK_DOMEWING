const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    const products = [];
    try {
        const args = process.argv.slice(2);
        const [filePath, username, password] = args;
        await page.goto('https://www.ds1008.com/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('#loginId', username);
        await page.type('#loginPwd', password);
        await page.click('#formLogin > div.login > button');
        await page.waitForNavigation();

        // 파일에서 URL과 코드를 읽어오기
        const items = JSON.parse(fs.readFileSync(filePath, 'utf8'));

        for (const item of items) {
            const { href, code } = item;  // href와 code 추출
            await page.goto(href, { waitUntil: 'networkidle2' });
            const productContents = await page.evaluate((href, code) => {
                const toAbsoluteUrl = (src, baseUrl) => {
                    if (src.startsWith('http://') || src.startsWith('https://')) {
                        return src;
                    } else {
                        return new URL(src, baseUrl).href;
                    }
                };
                const productName = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2').textContent.trim();
                const baseUrl = window.location.origin;
                const images = document.querySelectorAll('#detail > div.txt-manual img');
                const productDetail = Array.from(images, img => toAbsoluteUrl(img.getAttribute('src'), baseUrl));

                return {
                    productName,
                    productDetail,
                    productHref: href,
                    productCode: code,  // 제품 코드 추가
                };
            }, href, code);
            products.push(productContents);
        }

        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
    return products;
})();
