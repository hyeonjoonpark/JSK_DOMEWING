const puppeteer = require('puppeteer'); // Puppeteer 모듈을 불러옵니다.
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.

(async () => {
    const browser = await puppeteer.launch({ headless: true }); // 브라우저 인스턴스를 headless 모드로 실행합니다.
    const page = await browser.newPage(); // 새로운 페이지(탭)을 엽니다.
    try {
        const args = process.argv.slice(2); // 커맨드 라인 인자를 가져옵니다.
        const [tempFilePath, username, password] = args; // 인자에서 파일 경로, 사용자 이름, 비밀번호를 추출합니다.
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8')); // URL 목록이 담긴 파일을 읽어서 파싱합니다.

        await signIn(page, username, password); // 로그인 함수를 호출합니다.

        const products = []; // 스크래핑된 상품 정보를 저장할 배열을 초기화합니다.
        for (const url of urls) { // URL 목록을 순회합니다.
            const navigateWithRetryResult = await navigateWithRetry(page, url); // 페이지 이동 시도합니다. 실패하면 다음 URL로 넘어갑니다.
            if (navigateWithRetryResult === false) {
                continue;
            }
            const product = await scrapeProduct(page, url); // 현재 URL에서 상품 정보를 스크래핑합니다.
            if (product === false) { // 스크래핑이 실패하면 다음 URL로 넘어갑니다.
                continue;
            }
            products.push(product); // 스크래핑 성공 시 상품 정보를 배열에 추가합니다.
        }
        console.log(JSON.stringify(products)); // 모든 상품 정보를 JSON 형식으로 출력합니다.
    } catch (error) {
        console.error('Error occurred:', error); // 오류 발생 시 오류 메시지를 출력합니다.
    } finally {
        await browser.close(); // 작업이 끝나면 브라우저를 닫습니다.
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

        const baseUrl = 'https://housemore.co.kr/';
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
