const puppeteer = require('puppeteer'); // Puppeteer 모듈을 불러옵니다.
const fs = require('fs'); // 파일 시스템 모듈을 불러옵니다.

(async () => {
    const browser = await puppeteer.launch({ headless: true }); // 브라우저 인스턴스를 headless 모드로 실행합니다.
    const page = await browser.newPage(); // 새로운 페이지(탭)을 엽니다.
    try {
        const args = process.argv.slice(2); // 커맨드 라인 인자를 가져옵니다.
        const [tempFilePath, username, password] = args; // 인자에서 파일 경로, 사용자 이름, 비밀번호를 추출합니다.
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8')); // URL 목록이 담긴 파일을 읽어서 파싱합니다.
        // const urls = ['https://www.jhmungu.com/shop/goods_detail.php?ps_uid=157686'];
        // const username = "jskorea2023";
        // const password = "Tjddlf88!@#";
        await signIn(page, username, password); // 로그인 함수를 호출합니다.

        const products = []; // 스크래핑된 상품 정보를 저장할 배열을 초기화합니다.
        for (const url of urls) { // URL 목록을 순회합니다.
            await page.goto(url, { waitUntil: 'domcontentloaded' });
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
    } ``
})();
async function signIn(page, username, password) {
    await page.goto('https://www.jhmungu.com/shop/login.php', { waitUntil: 'networkidle0' });
    await page.type('#memberLogin > div > div:nth-child(1) > form > div:nth-child(2) > input[type=text]:nth-child(1)', username);
    await page.type('#memberLogin > div > div:nth-child(1) > form > div:nth-child(2) > input.mt-2', password);
    await page.click('#memberLogin > div > div:nth-child(1) > form > div.form-group.row.text-center > div.col-12.col-md > button');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {
        const tagList = document.querySelectorAll('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > div.d-flex.flex-wrap.justify-content-between.mb-3.pb-2.border-bottom > div.col-auto.d-flex > p span');
        if (tagList) {
            for (const tag of tagList) {
                const tagText = tag.textContent.trim();
                if (tagText.includes('배송불가') || tagText.includes('안함') || tagText.includes('품절') || tagText.includes('미정') || tagText.includes('단종') || tagText.includes('반품불가')) {
                    return false;
                }
            }
        }
        // 상품명
        const productName = document.querySelector('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > h5 > span').textContent.trim();
        // 상품 가격
        const productPrice = document.querySelector('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > div.row.align-items-center.detail-price-info.justify-content-end > div.col-md.col-7 > div > div:nth-child(2) > span').textContent.trim().replace(/[^\d]/g, '');

        const productImageElement = document.querySelector('#goodsDetailImage').getAttribute('src').trim();
        const productImage = productImageElement.startsWith('http') ? productImageElement : `https:${productImageElement}`;

        const baseUrl = 'https://www.jhmungu.com/shop/';
        const toAbsoluteUrl = (relativeUrl, baseUrl) => new URL(relativeUrl, baseUrl).toString();

        const getAbsoluteImageUrls = (nodeList, baseUrl, ...excludedPaths) =>
            [...nodeList]
                .filter(img => !excludedPaths.some(path => img.src.includes(path)))
                .map(img => toAbsoluteUrl(img.src, baseUrl));

        const productDetailImageElements = document.querySelectorAll('#menu_explain > div:nth-child(3) img');
        const excludedPaths = ['/web/img/start', '/web/img/event'];
        const productDetail = getAbsoluteImageUrls(productDetailImageElements, baseUrl, ...excludedPaths);
        if (productDetail.length < 1) {
            productDetail.push(productImage);
        }

        let hasOption = false;
        let productOptions = [];
        const optionElement = document.querySelector('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > dl > dd > select');
        if (optionElement) {
            hasOption = true;
            // 모든 옵션을 선택합니다.
            let optionElements;
            const optionType = document.querySelector('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > dl > dd > select > option');
            if (optionType) {
                optionElements = document.querySelectorAll('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > dl > dd > select option');
            } else {
                optionElements = document.querySelectorAll('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > dl > dd option');
                optionElements = Array.from(optionElements).filter(option => !option.value.includes('*'));
            }

            let isFirstOption = true; // 첫 번째 옵션을 추적하기 위한 변수
            for (const optionElement of optionElements) {
                if (isFirstOption) {
                    isFirstOption = false; // 첫 번째 옵션을 처리했으므로, 플래그를 false로 설정
                    continue; // 첫 번째 옵션을 건너뜁니다.
                }

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
            sellerID: 27
        };
    }, productHref);
    return product;
}
