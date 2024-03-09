const puppeteer = require('puppeteer'); // Puppeteer 모듈을 가져옵니다.
const fs = require('fs'); // 파일 시스템 모듈을 가져옵니다.

(async () => {
    const browser = await puppeteer.launch({ headless: false }); // 헤드리스 모드로 Puppeteer를 실행합니다.
    const page = await browser.newPage(); // 새 페이지를 생성합니다.
    try {
        // const [tempFilePath, username, password] = process.argv.slice(2); // 명령줄 인수를 가져옵니다.
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8')); // JSON 파일에서 URL 목록을 읽어옵니다.
        const urls = ['https://www.kapoka.co.kr/shop/goods/goods_view.php?goodsno=3188&category=033010'];
        const username = 'sungiltradekorea';
        const password = 'tjddlf88!@#';
        await sign(page, username, password); // 웹사이트에 로그인합니다.
        const products = []; // 제품 데이터를 수집할 배열을 초기화합니다.
        for (const url of urls) { // URL 목록을 반복하며
            await page.goto(url, { waitUntil: 'domcontentloaded' }); // 페이지를 해당 URL로 이동합니다.
            const product = await scrapeProduct(page, url); // 제품 데이터를 스크레이핑합니다.
            if (product) { // 제품이 유효한 경우
                products.push(product); // 제품 배열에 추가합니다.
            }
        }
        console.log(JSON.stringify(products)); // 수집된 제품 데이터를 JSON 형식으로 출력합니다.
    } catch (error) { // 오류가 발생한 경우
        console.error('Error occurred:', error); // 에러를 콘솔에 출력합니다.
    } finally { // 마지막에
        await browser.close(); // 브라우저를 닫습니다.
    }
})();

async function sign(page, username, password) {
    // 로그인 페이지로 이동합니다.
    await page.goto('https://www.kapoka.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    // 사용자 이름과 비밀번호 입력 필드에 값을 입력합니다.
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(3) > td:nth-child(2) > input[type=password]', password);
    // 로그인 버튼을 클릭합니다.
    await Promise.all([
        page.click('#form > table > tbody > tr:nth-child(2) > td[class="noline"]'), // 로그인 버튼 클릭
        page.waitForNavigation({ waitUntil: 'networkidle0' }) // 페이지 내비게이션이 완료될 때까지 대기
    ]);
}

async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await page.evaluate(() => {
            // 제품 가격을 가져옵니다.
            const productPriceText = document.querySelector('#price').textContent.trim();
            const productPrice = parseInt(productPriceText.replace(/[^\d]/g, '')); // 숫자로만 이루어진 가격
            return productPrice;
        });
        // getHasOption 함수를 통해 옵션 관련 정보를 가져옵니다.
        const { hasOption, productOptions } = await getHasOption(page, productPrice);

        // 페이지의 DOM을 직접 조작하여 제품 정보를 추출합니다.
        return await page.evaluate((productHref, hasOption, productOptions, productPrice) => {
            // 제품 이름을 가져옵니다.
            const productNameElement = document.querySelector('div:nth-child(2) > a');
            const nameText = productNameElement.textContent.trim();
            const productName = nameText.replace(/\[[^\]]*\]/g, ''); // 불필요한 태그를 제거한 상품명

            // 제품 이미지 URL을 가져옵니다.
            const imageElement = document.querySelector('#objImg');
            const productImage = imageElement.src;
            // 제품 상세 설명 이미지 URL을 가져옵니다.
            const productDetailElements = document.querySelectorAll('#contents > table > tbody > tr > td > p img');
            const productDetail = [];
            for (const productdetailElement of productDetailElements) {
                const productDetailSrc = productdetailElement.src;
                if (!productDetailSrc.includes('img_02') && !productDetailSrc.includes('img_03') && !productDetailSrc.includes('info_01')) {
                    productDetail.push(productDetailSrc);
                }
            }


            // 제품 정보를 객체로 반환합니다.
            return {
                productName: productName,
                productPrice: productPrice,
                productImage: productImage,
                productDetail: productDetail,
                hasOption: hasOption,
                productOptions: productOptions,
                productHref: productHref,
                sellerID: 22
            };
        }, productHref, hasOption, productOptions, productPrice);
    } catch (error) {
        console.error('Error occurred while scraping product:', error);
        return null;
    }
}

async function getHasOption(page, productPrice) {
    productPrice = parseInt(productPrice, 10);
    try {
        const productOptions = [];
        const optionGroups = await page.$$('div > select');

        for (const group of optionGroups) {
            const options = await group.$$('option');

            // 첫 번째 옵션을 건너뛰기 위해 인덱스를 사용한 for 루프로 변경
            for (let i = 1; i < options.length; i++) { // 0 대신 1부터 시작하여 첫 번째 옵션을 건너뜁니다.
                const option = options[i]; // 현재 옵션
                const optionText = await (await option.getProperty('textContent')).jsonValue();

                if (optionText.includes('품절')) {
                    continue;
                }

                let optionName, optionPrice;
                const regex = /(.+)\s\(([\d,]+)원\)/;
                const matches = optionText.match(regex);
                if (matches) {
                    optionName = matches[1].trim();
                    optionPrice = parseInt(matches[2].replace(/[^\d-+]/g, ''), 10);
                    optionPrice = optionPrice - productPrice;
                } else {
                    optionName = optionText.trim();
                    optionPrice = 0;
                }

                productOptions.push({ optionName, optionPrice });
            }
        }

        return { hasOption: productOptions.length > 0, productOptions: productOptions };
    } catch (error) {
        console.error('Error occurred while getting options:', error);
        return { hasOption: true, productOptions: [] };
    }
}
