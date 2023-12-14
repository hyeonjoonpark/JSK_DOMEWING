const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        await page.goto('https://www.metaldiy.com/login/popupLogin.do?popupYn=Y');
        await page.waitForSelector('#loginId');
        await page.waitForSelector('#loginPw');
        await page.waitForSelector('#wrapper > div > div.popup_login > div.login_box > fieldset > div > div.login_btn > input[type=image]');
        await page.type('#loginId', username);
        await page.type('#loginPw', password);
        await page.click('#wrapper > div > div.popup_login > div.login_box > fieldset > div > div.login_btn > input[type=image]');
        await page.waitForNavigation();
        await page.goto(productHref, { waitUntil: 'networkidle2' });
        // await new Promise((page) => setTimeout(page, 3000));
        // await page.waitForSelector('#webItemNm');
        // await page.waitForSelector('#zoom_goods');
        // await page.waitForSelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li.price > dl:nth-child(3) > dd > span');
        // await page.waitForSelector('#detail > img');
        // Extract product details
        const productContents = await page.evaluate((productHref) => {
            const productName = document.querySelector('#webItemNm').value;
            // 할인된 가격이 있는지 확인
            let discountedPriceElement = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li.price > dl:nth-child(3) > dd > strike > span');
            let originalPriceElement = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li.price > dl:nth-child(3) > dd > span');
            let productPrice;
            if (discountedPriceElement) {
                // 할인된 가격이 있으면 그 값을 사용
                productPrice = discountedPriceElement.textContent;
            } else if (originalPriceElement) {
                // 할인된 가격이 없으면 정가를 사용
                productPrice = originalPriceElement.textContent;
            }
            // 가격에서 숫자만 추출
            productPrice = productPrice.replace(/[^\d]/g, '');
            productPrice = parseInt(productPrice);
            const productImage = document.querySelector('#zoom_goods').src;
            const baseUrl = window.location.origin;
            // 'detail' 아이디를 가진 요소 내의 모든 <img> 태그를 선택합니다.
            const images = document.querySelectorAll('#detail img');
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
            let productOptionEle = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li:nth-child(5) > dl > dd > table > tbody');
            let hasOption = false;
            let productOptions = [];
            if (productOptionEle !== null) {
                hasOption = true;
                const optionElements = document.querySelectorAll('#container > div.container.wrapper_fix > div > div.goods_info > div.right > ul > li:nth-child(5) > dl > dd > table > tbody > tr');
                optionElements.forEach(el => {
                    const optionName = el.querySelector('.op_name').textContent.trim();
                    let optionPrice = el.querySelector('.price > span').textContent.trim();
                    // 가격에서 숫자만 추출 (정규식을 사용하여 숫자만 남김)
                    optionPrice = optionPrice.replace(/[^\d]/g, '');
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
                sellerID: 2
            };
        }, productHref);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
