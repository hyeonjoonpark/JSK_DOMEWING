const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();
    const products = [];
    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        await page.goto('https://www.ds1008.com/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('#loginId', username);
        await page.type('#loginPwd', password);
        await page.click('#formLogin > div.login > button');
        await page.waitForNavigation();
        // await page.goto(productHref, { waitUntil: 'networkidle2' });

        const urls = JSON.parse(fs.readFileSync(productHref, 'utf8'));

        for (const url of urls) {
            await page.goto(url, { waitUntil: 'networkidle2' });
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
                const originalPriceElement = document.querySelector('#frmView > input[type=hidden]:nth-child(13)').value;
                const baseUrl = window.location.origin;

                // 상품 상세 이미지 추출
                const images = document.querySelectorAll('#detail > div.txt-manual img');
                const productDetail = Array.from(images, img => toAbsoluteUrl(img.getAttribute('src'), baseUrl));

                // 옵션 확인
                const hasOptionEle = document.querySelector('#frmView > input[type=hidden]:nth-child(27)').value;
                let hasOption = hasOptionEle === 'y';
                let productOptions = [];

                if (hasOption) {
                    const optionElements = document.querySelectorAll('#frmView > div > div.choice > div > div > select > option');

                    // Use array destructuring and Array.slice to skip the first element
                    Array.from(optionElements).slice(1).forEach(el => {
                        const text = el.textContent;
                        // Split text into optionName and optionPriceText using array destructuring
                        const [optionName, optionPriceText] = text.split(' : ').map(s => s.trim());
                        if (!optionName.includes('품절')) {
                            // Parse the price as an integer
                            let optionPrice = 0;
                            if (optionPriceText) {
                                optionPrice = parseInt(optionPriceText.replace(/[^\d]/g, ''), 10);
                            }
                            // Push the object to productOptions
                            productOptions.push({ optionName, optionPrice });
                        }
                    });
                }

                return {
                    productName,
                    // productPrice,
                    // productImage,
                    productDetail,
                    // hasOption,
                    // productOptions,
                    // productHref,
                    // sellerID: 14
                };
            }, productHref);
            products.push(productContents);
        }


        console.log(JSON.stringify(products)); // 수정된 부분
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
    return products;
})();
