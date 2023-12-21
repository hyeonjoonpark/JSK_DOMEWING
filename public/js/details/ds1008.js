const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        await page.goto('https://www.ds1008.com/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('#loginId', username);
        await page.type('#loginPwd', password);
        await page.click('#formLogin > div.login > button');
        await page.waitForNavigation();
        await page.goto(productHref, { waitUntil: 'networkidle2' });
        const productContents = await page.evaluate((productHref) => {
            // 절대 URL 변환 함수
            const toAbsoluteUrl = (src, baseUrl) => {
                if (src.startsWith('http://') || src.startsWith('https://')) {
                    return src;
                } else {
                    return new URL(src, baseUrl).href;
                }
            };

            const productName = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2').textContent.trim();
            const originalPriceElement = document.querySelector('#frmView > div > div.item > ul > li.price > div > strong').textContent.trim();
            let productPrice = parseInt(originalPriceElement.replace(/[^\d]/g, ''), 10);
            const productImage = document.querySelector('#mainImage > img').src;
            const baseUrl = window.location.origin;

            // 상품 상세 이미지 추출
            const images = document.querySelectorAll('#detail > div.txt-manual img');
            const productDetail = Array.from(images, img => toAbsoluteUrl(img.getAttribute('src'), baseUrl));

            // 옵션 확인
            const hasOptionEle = document.querySelector('#frmView > input[type=hidden]:nth-child(27)').value;
            let hasOption = hasOptionEle === 'y';
            let productOptions = [];

            if (hasOption) {
                const optionElements = document.querySelectorAll('#frmView > div > div.choice > div > div > div > div > ul > li');

                // Use array destructuring and Array.slice to skip the first element
                Array.from(optionElements).slice(1).forEach(el => {
                    const text = el.textContent;
                    // Split text into optionName and optionPriceText using array destructuring
                    const [optionName, optionPriceText] = text.split(':').map(s => s.trim());
                    // Parse the price as an integer
                    let optionPrice = 0;
                    if (optionPriceText) {
                        optionPrice = parseInt(optionPriceText.replace(/[^\d]/g, ''), 10);
                    }
                    // Push the object to productOptions
                    productOptions.push({ optionName, optionPrice });
                });
            }

            return {
                productName,
                productPrice,
                productImage,
                productDetail,
                hasOption,
                productOptions,
                productHref,
                sellerID: 14
            };
        }, productHref);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
