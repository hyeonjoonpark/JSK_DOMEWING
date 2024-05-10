const puppeteer = require('puppeteer');
const fs = require('fs');
const delay = (time) => new Promise(resolve => setTimeout(resolve, time));

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const items = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // 로그인 프로세스
        await page.goto('https://vitsonmro.com/mro/login.do', { waitUntil: 'networkidle0' });
        await page.type('#custId', username);
        await page.type('#custPw', password);
        await page.click('#loginForm > div > a:nth-child(3)');
        await page.waitForSelector('#wrap');

        const products = [];
        for (const item of items) {
            const { href, code } = item;
            await page.goto(href, { waitUntil: 'domcontentloaded', timeout: 60000 });

            // 스크래핑 로직
            const product = await page.evaluate((href, code) => {
                const images = document.querySelectorAll('#detail_box > div > ul img');
                const productDetail = Array.from(images, img => {
                    let src = img.getAttribute('src');
                    if (src.endsWith('.jpg')) { // .jpg 파일만 필터링
                        return src;
                    }
                    return null; // .jpg가 아닌 파일은 null 반환
                }).filter(src => src !== null); // null 값 제거하여 최종 배열 생성

                return {
                    productDetail: productDetail,
                    productCode: code // 제품 코드를 추가
                };
            }, href, code); // href와 code를 인자로 전달합니다.


            if (product === false) {
                continue;
            }

            products.push(product);
        }

        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
