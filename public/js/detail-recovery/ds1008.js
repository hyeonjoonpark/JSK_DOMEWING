const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    const products = [];
    try {
        const args = process.argv.slice(2);
        const [filePath, username, password] = args;
        await page.goto('https://www.ds1008.com/member/login.php', { waitUntil: 'networkidle0' });
        await page.type('#loginId', username);
        await page.type('#loginPwd', password);
        await page.click('#formLogin > div.login > button');
        await page.waitForNavigation();

        // 파일에서 URL과 코드를 읽어오기
        const items = JSON.parse(fs.readFileSync(filePath, 'utf8'));

        for (const item of items) {
            try {
                await page.goto(href, { waitUntil: 'domcontentloaded' });
                const productContents = await page.evaluate((item) => {
                    const toAbsoluteUrl = (src, baseUrl) => {
                        if (src.startsWith('http://') || src.startsWith('https://')) {
                            return src;
                        } else {
                            return new URL(src, baseUrl).href;
                        }
                    };
                    const baseUrl = window.location.origin;
                    const images = document.querySelectorAll('#detail > div.txt-manual img');
                    const newProductDetail = Array.from(images, img => toAbsoluteUrl(img.getAttribute('src'), baseUrl));

                    return {
                        id: item.id,
                        newProductDetail,
                        originProductDetail: item.productDetal
                    };
                }, item);
                products.push(productContents);
            } catch (error) {
                continue;
            }
        }

        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
    return products;
})();
