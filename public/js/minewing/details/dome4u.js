const puppeteer = require('puppeteer'); // Puppeteer 라이브러리를 가져옵니다.
const fs = require('fs'); // 파일 시스템 관련 작업을 위해 Node.js의 fs 모듈을 가져옵니다.

// 비동기 IIFE (즉시 실행 함수 표현)을 사용하여 비동기 작업을 관리합니다.
(async () => {
    const browser = await puppeteer.launch({ headless: false }); // headless 브라우저 인스턴스를 시작합니다.
    const page = await browser.newPage(); // 새로운 페이지(탭)를 엽니다.
    try {
        const args = process.argv.slice(2); // 커맨드라인 인자를 배열로 가져옵니다.
        const [tempFilePath, username, password] = args; // 구조 분해 할당을 사용해 변수에 값을 할당합니다.
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8')); // 임시 파일에서 URL 목록을 읽어 파싱합니다.
        await signIn(page, username, password); // 로그인 함수를 호출하여 로그인 과정을 수행합니다.
        const products = []; // 스크랩된 상품 정보를 저장할 배열을 초기화합니다.
        for (const url of urls) { // URL 배열을 순회합니다.
            const navigateWithRetryResult = await navigateWithRetry(page, url); // 페이지 이동을 시도하며, 필요시 재시도합니다.
            if (navigateWithRetryResult === false) { // 이동 실패시 다음 URL로 넘어갑니다.
                continue;
            }
            const product = await scrapeProduct(page, url); // 상품 정보를 스크랩하는 함수를 호출합니다.
            if (product === false) { // 스크랩에 실패한 경우, 다음 URL로 넘어갑니다.
                continue;
            }
            products.push(product); // 스크랩된 상품 정보를 배열에 추가합니다.
        }
        console.log(JSON.stringify(products)); // 최종적으로 수집된 상품 정보를 출력합니다.
    } catch (error) {
        console.error('Error occurred:', error); // 오류 발생시 콘솔에 에러 메시지를 출력합니다.
    } finally {
        await browser.close(); // 작업 완료 후 브라우저를 닫습니다.
    }
})();

// navigateWithRetry 함수는 주어진 URL로의 이동을 시도하며, 실패시 지정된 횟수만큼 재시도합니다.
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' }); // 페이지 이동을 시도합니다.
            return true; // 이동 성공시 true를 반환합니다.
        } catch (error) {
            if (i < attempts - 1) { // 마지막 시도가 아닌 경우 지정된 시간만큼 대기 후 재시도합니다.
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    return false; // 모든 시도가 실패한 경우 false를 반환합니다.
}

// signIn 함수는 주어진 페이지에서 로그인 과정을 수행합니다.
async function signIn(page, username, password) {
    await page.goto('https://dome4u.co.kr/home/member/login.php', { waitUntil: 'networkidle0' }); // 로그인 페이지로 이동합니다.
    await page.type('#id', username); // 사용자 이름을 입력합니다.
    await page.type('#passwd', password); // 비밀번호를 입력합니다.
    await page.click('#login_submit'); // 로그인 버튼을 클릭합니다.
    await page.waitForNavigation(); // 네비게이션이 완료될 때까지 대기합니다.
}

// scrapeProduct 함수는 주어진 페이지에서 상품 정보를 추출합니다.
async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await page.evaluate(() => {
            const priceSelector = '#buy_info > div > div.detailView.type2 > dl > dd.priceView > ul > li:nth-child(1) > strong';
            const productPriceText = document.querySelector(priceSelector)?.textContent.trim();
            const productPrice = parseInt(productPriceText.replace(/[^\d]/g, ''), 10);
            return productPrice;
        });

        if (!productPrice) {
            console.error('Product price could not be scraped.');
            return null;
        }

        const { hasOption, productOptions } = await getHasOption(page, productPrice);

        return await page.evaluate((productHref, hasOption, productOptions, productPrice) => {
            try {
                const productNameSelector = '#buy_info > div > div.detailView.type2 > dl > dt > span';
                const productNameElements = document.querySelectorAll(productNameSelector);
                const productNameElement = Array.from(productNameElements).find(el => !el.classList.contains('ownershop'));
                const productName = productNameElement ? productNameElement.textContent.trim() : '';

                const imageSelector = '#viewImage_m';
                const imageElement = document.querySelector(imageSelector);
                const productImage = imageElement ? imageElement.src : '';

                const detailSelector = '#content .detailInfo.detailInner img';
                const productDetailElement = document.querySelector(detailSelector);
                const productDetail = productDetailElement ? productDetailElement.src : null;

                return {
                    productName,
                    productPrice,
                    productImage,
                    productDetail,
                    hasOption,
                    productOptions,
                    productHref,
                    sellerID: 32
                };
            } catch (error) {
                console.error('Error in product details extraction:', error);
                return false;
            }
        }, productHref, hasOption, productOptions, productPrice);
    } catch (error) {
        console.error('Error occurred while scraping product:', error);
        return null;
    }
}

async function getHasOption(page, productPrice) {
    productPrice = parseInt(productPrice, 10);
    const optionSelector = '#buy_info > div > div.detailView.type2 > dl > dd.optionView > select';

    const productOptions = [];

    try {
        const options = await page.$$(`${optionSelector} > option`);

        // 첫 번째 옵션('선택하세요'와 같은 문구가 포함됨)을 제외하고 처리
        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            const optionText = await (await option.getProperty('textContent')).jsonValue();

            if (optionText.includes('품절')) continue;

            let optionName, optionPriceDiff;
            const regex = /(.+)\s\(([\d,]+)원\)/;
            const matches = optionText.match(regex);

            if (matches) {
                optionName = matches[1].trim();
                let optionPrice = parseInt(matches[2].replace(/[^\d]/g, ''), 10);
                optionPriceDiff = optionPrice - productPrice;
            } else {
                optionName = optionText.trim();
                optionPriceDiff = 0; // 추가 비용 없음
            }

            productOptions.push({ optionName, optionPriceDiff });
        }

        return { hasOption: productOptions.length > 0, productOptions };
    } catch (error) {
        console.error('Error occurred while getting options:', error);
        return { hasOption: false, productOptions: [] };
    }
}
