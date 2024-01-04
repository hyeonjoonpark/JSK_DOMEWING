const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        await page.goto('http://babonara.co.kr/shop/member/login.php', { waitUntil: 'networkidle2' });
        await page.type('input[name="m_id"]', username);
        await page.type('input[name="password"]', password);
        await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
        await page.waitForNavigation();
        await page.goto(productHref, { waitUntil: 'load' });
        const productContents = await page.evaluate((productHref) => {
            // 절대 URL 변환 함수
            const toAbsoluteUrl = (src, baseUrl) => {
                if (src.startsWith('http://') || src.startsWith('https://')) {
                    return src;
                } else {
                    return new URL(src, baseUrl).href;
                }
            };
            const productName = document.querySelector('#goods_spec > form > div:nth-child(4) > b').textContent.trim();
            let productPrice = document.querySelector('#price').textContent.trim();
            // 가격에서 숫자만 추출
            productPrice = productPrice.replace(/[^\d]/g, '');
            productPrice = parseInt(productPrice);
            const productImage = document.querySelector('#objImg').src;
            const baseUrl = window.location.origin;
            // 'detail' 아이디를 가진 요소 내의 모든 <img> 태그를 선택합니다.
            const images = document.querySelectorAll('#contents > table > tbody > tr > td img');
            // 각 이미지의 src 속성을 절대 경로로 변환합니다.
            const productDetail = Array.from(images, img => {
                let src = img.getAttribute('src');
                // 상대 경로인 경우 baseUrl을 추가합니다.
                if (src.startsWith('http://') || src.startsWith('https://')) {
                    return src; // 이미 절대 경로인 경우
                } else {
                    return new URL(src, baseUrl).href; // 상대 경로를 절대 경로로 변환
                }
            });
            let productOptionEle = document.querySelector('#goods_spec > form > table:nth-child(9) > tbody > tr:nth-child(2) > td > div > select');
            let hasOption = false;
            let productOptions = [];
            if (productOptionEle !== null) {
                hasOption = true;
                const optionElements = document.querySelectorAll('#goods_spec > form > table:nth-child(9) > tbody > tr:nth-child(2) > td > div > select > option');
                optionElements.forEach(el => {
                    const optionName = el.value.trim();
                    let optionPrice = 0;
                    productOptions.push({ optionName, optionPrice: parseInt(optionPrice) });
                });
            }
            return {
                productName: productName,
                productPrice: productPrice,
                productImage: productImage,
                productDetail: productDetail,
                hasOption: hasOption,
                productOptions: productOptions,
                productHref: productHref,
                sellerID: 1
            };
        }, productHref);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
