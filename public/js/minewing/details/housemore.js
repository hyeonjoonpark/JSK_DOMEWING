const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const product = await scrapeProduct(page, url);
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
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            return true;
        } catch (error) {
            if (i < attempts - 1) {
                await new Promise(resolve => setTimeout(resolve, 1000));
            }
        }
    }
    return false;
}
async function signIn(page, username, password) {
    await page.goto('https://housemore.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await page.evaluate(async () => {
        const distance = 45;
        const scrollInterval = 50;
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.getElementById('prdDetail');
            const prdInfoElement = document.getElementById('prdInfo');
            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                break;
            } else {
                window.scrollBy(0, distance);
            }

            await new Promise(resolve => setTimeout(resolve, scrollInterval));
        }
    });
    await new Promise(resolve => setTimeout(resolve, 1000)); // 페이지 로드 후 1초 대기
    const product = await page.evaluate((productHref) => {
        const regex = /\([^()]*\)/g;
        const productNameText = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.headingArea > h2').textContent.trim();
        const productName = productNameText.replace(regex, '');

        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');

        const productImageElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > div > a > img').getAttribute('src').trim();
        const productImage = productImageElement.startsWith('http') ? productImageElement : `https:${productImageElement}`;

        const baseUrl = 'https://candle-box.com/';
        const toAbsoluteUrl = (relativeUrl, baseUrl) => new URL(relativeUrl, baseUrl).toString();

        const getAbsoluteImageUrls = (nodeList, baseUrl, ...excludedPaths) =>
            [...nodeList]
                .filter(img => !excludedPaths.some(path => img.src.includes(path)))
                .map(img => toAbsoluteUrl(img.src, baseUrl));

        const productDetailImageElements = document.querySelectorAll('#prdDetail img');
        const excludedPaths = ['/web/img/start', '/web/img/event'];
        const productDetail = getAbsoluteImageUrls(productDetailImageElements, baseUrl, ...excludedPaths);

        let hasOption = false;
        let productOptions = [];
        const optionElement = document.querySelector('#product_option_id1');
        if (optionElement) {
            hasOption = true;
            // 모든 옵션을 선택합니다.
            let optionElements;
            const optionType = document.querySelector('#product_option_id1 > optgroup');
            if (optionType) {
                optionElements = document.querySelectorAll('#product_option_id1 > optgroup option');
            } else {
                optionElements = document.querySelectorAll('#product_option_id1 option');
                optionElements = Array.from(optionElements).filter(option => !option.value.includes('*'));
            }
            for (const optionElement of optionElements) {
                const optionText = optionElement.textContent.trim();
                // '품절' 텍스트가 포함되어 있다면, 이 옵션을 건너뜁니다.
                if (optionText.includes('품절')) {
                    continue;
                }

                let optionName = null, optionPrice = 0;
                if (optionText.includes('원)')) {
                    // 옵션 텍스트에서 이름과 가격을 분리합니다.
                    const [name, price] = optionText.split(' (');
                    optionName = name.trim();
                    optionPrice = parseInt(price.replace(/[^\d-+]/g, ''), 10);
                } else {
                    optionName = optionText;
                }

                productOptions.push({ optionName, optionPrice });
            }
        }


        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 25
        };
    }, productHref);
    return product;
}
