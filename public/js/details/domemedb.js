const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    try {
        const href = process.argv[2];
        await page.goto(href);
        await page.waitForSelector('#lInfoViewItemContents');
        const productDetail = await page.evaluate(() => {
            const container = document.querySelector('#lInfoViewItemContents');
            const elements = container.children;

            // script 태그를 제외한 HTML 문자열 생성
            let resultHtml = '';
            for (const element of elements) {
                if (element.tagName.toLowerCase() !== 'script') {
                    resultHtml += element.outerHTML;
                }
            }

            return resultHtml;
        });

        const data = {
            productDetail: productDetail
        };
        console.log(JSON.stringify(data));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();