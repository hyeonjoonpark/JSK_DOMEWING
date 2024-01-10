const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [productHref, username, password] = args;
        // Sign-in.
        await page.goto('https://dometopia.com/member/login', { waitUntil: 'networkidle2', timeout: 0 });
        await page.type('#userid', username);
        await page.type('#password', password);
        await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
        await page.waitForNavigation();
        await page.goto(productHref, { waitUntil: 'load', timeout: 0 });
        const productContents = await page.evaluate((productHref) => {
            const thirdImage = document.querySelector('#goods_thumbs > div.box_thumbs > ul > li:nth-child(3) > a');
            if (thirdImage) {
                thirdImage.click();
            }
            const baseURL = 'https://dometopia.com';
            const productName = document.querySelector('#info > div.goods_info.clearbox > form > div.container > div > h2').textContent.trim();
            let productPrice = document.querySelector('#info > div.goods_info.clearbox > form > div.container > table > tbody > tr:nth-child(2) > td > ul:nth-child(2) > li:nth-child(3) > span').textContent.trim();
            // 가격에서 숫자만 추출
            productPrice = productPrice.replace(/[^\d]/g, '');
            productPrice = parseInt(productPrice);
            const productImage = document.querySelector(`img[onerror="this.src='/data/skin/beauty/images/common/noimage.gif'"]`).src;
            // 'detail' 아이디를 가진 요소 내의 모든 <img> 태그를 선택합니다.
            const images = document.querySelectorAll('#detail > div > div.section.info > div.goods_description > div.detail-img img');
            const productDetail = images.length === 0 ? [] : Array.from(images, img => {
                let src = img.getAttribute('src');
                // Check if the src is an absolute URL
                if (src.startsWith('http://') || src.startsWith('https://')) {
                    return src; // Return the src if it's already an absolute URL
                } else {
                    return new URL(src, baseURL).href; // Convert relative URL to absolute URL
                }
            });
            let hasOption = false;
            let productOptions = [];
            return {
                productName: productName,
                productPrice: productPrice,
                productImage: productImage,
                productDetail: productDetail,
                hasOption: hasOption,
                productOptions: productOptions,
                productHref: productHref,
                sellerID: 3
            };
        }, productHref);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();