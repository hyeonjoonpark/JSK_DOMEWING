const puppeteer = require('puppeteer'); // 변수선언
(async () => { // 즉시 실행 함수를 사용하여 비동기 코드를 실행시킨다
    const browser = await puppeteer.launch({ headless: false }); // 퍼피티어 모듈에서 런치 메서드를 호출 브라우저 실행 헤드레스 옵션으로 인터페이스 없이 백그라운드에서 실행
    const page = await browser.newPage(); // 변수 페이지 브라우저에서 뉴페이지 메서드를 호출하는?
    const args = process.argv.slice(2); // args 이거 아마 터미널 입력 두번째 코드부터 실행시키는 코드였던걸로 기억
    try { // 시도하다
        const [listURL, username, password] = args; // 변수 순서대로 입력하게 만드는 코드라고 생각중 args의 개념을 아직 모르는듯?
        await login(page, username, password); // 기다렸다가 로그인 page id pw 순서 이게 아마 로그인함수를 실행시키는 재료인듯?
        await processPage(page, listURL); // 기다렸다가 과정 페이지 processPage 무슨 변수명인지 모르겠음
        const products = await scrapeProducts(page); // 변수 프로덕트 선언하는건가 스크랩프로덕트 즉 상품을 스크랩하는 페이지에서 상품을 긁어오는 역할인가?
        console.log(JSON.stringify(products)); // 콘솔 로그로 출력 시키는거 같은데 json 뭐 등등 무슨 코드인지 이해가 안감 ㅠ
    } catch (error) { // 에러를 잡는
        console.error(error); // 에러가 잡히면 에러를 출력
    } finally { // 마침내?
        // browser.close();
    } // 위에건 아마 닫는 마침내 완성하면 닫으라는 코드
})(); // 닫는 코드?
async function login(page, username, password) { // async 아바타? 연동? 그런 개념 login 함수를 연동시키라는거임? 암튼 로그인 함수에는 페이지 id pw 를 넣은거고 실행시키려는듯?
    await page.goto('http://www.autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0', timeout: 0 }); // 기다렸다가 페이지로 가게하는 코드
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username); // id를 입력
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    await page.waitForNavigation();
}
async function processPage(page, listURL) {
    await page.goto(listURL, { waitUntil: 'networkidle0', timeout: 0 });
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#b_white > font > b').textContent;
        const numProducts = parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
        return numProducts;
    });
    listURL += '&page_num=' + numProducts;
    await page.goto(listURL, { waitUntil: 'networkidle0', timeout: 0 });
}
// 이 메소드는 BitsOnMRO를 사용하여 상품 정보를 스크래핑합니다.
// 모든 product 객체의 필드명은 일치해야 합니다. 오토카필을 사용해 셀렉터를 정확히 지정하고 필요에 따라 수정하세요.
// 문자열 처리를 위한 JavaScript 함수를 반드시 숙지하세요.
// 또한, 오토카필 문자열을 적절히 편집하여 각 필드의 데이터 타입에 맞게 조정하세요.
async function scrapeProducts(page) {
    const products = await page.evaluate(() => { // Puppeteer의 page.evaluate()를 사용하여 페이지 내에서 JavaScript를 실행합니다.
        function processProduct(productElement) { // 각 상품 요소를 처리하여 상품 정보를 추출하는 함수를 정의합니다.
            const stockText = productElement.querySelector('td:nth-child(9) > span.hdsp_bot').textContent.trim(); // 재고 여부를 나타내는 텍스트를 가져옵니다.
            if (stockText !== '재고보유') { // 만약 상품의 재고가 없다면,
                return false; // false를 반환하여 해당 상품을 건너뜁니다.
            }
            const productName = productElement.querySelector('#menuLayer > tbody > tr:nth-child(1) > td:nth-child(1) > a').textContent.trim(); // 상품명을 가져옵니다.
            const standard = productElement.querySelector('td:nth-child(6) > span.hdsp_bot').textContent.trim(); // 상품의 기준을 가져옵니다.
            const name = productName + ' ' + standard; // 상품명과 기준을 결합하여 전체 상품명을 생성합니다.
            const productPriceText = productElement.querySelector('td:nth-child(10) > span.hdsp_top.price_cr').textContent; // 상품 가격을 나타내는 텍스트를 가져옵니다.
            const price = productPriceText.replace(/[^0-9]/g, '').trim(); // 상품 가격을 숫자로 변환하여 저장합니다.
            const image = productElement.querySelector('td:nth-child(4) > div > img').getAttribute('src'); // 상품 이미지의 소스 URL을 가져옵니다.
            const productCode = productElement.querySelector('td:nth-child(5) > span.hdsp_top').textContent.replace(/[^0-9]/g, '').trim(); // 상품 코드를 가져옵니다.
            const href = 'http://autocarfeel.co.kr/shop/main/index.php' + productCode; // 각 상품의 상세 페이지로 이동할 URL을 생성합니다.
            const platform = '오토카필'; // 상품이 속한 플랫폼을 지정합니다.
            return { name, price, image, href, platform }; // 상품 정보를 객체로 반환합니다.
        }
        const productElements = document.querySelectorAll('#grid > div.k-grid-content.k-auto-scrollable > table > tbody tr'); // 상품 요소들을 선택합니다.
        const products = []; // 상품 정보를 저장할 배열을 초기화합니다.
        for (const productElement of productElements) { // 각 상품 요소에 대해 반복합니다.
            const result = processProduct(productElement); // 상품 정보를 추출합니다.
            if (result !== false) { // 만약 상품 정보가 유효하다면,
                products.push(result); // 상품 정보를 배열에 추가합니다.
            }
        }
        return products; // 추출된 모든 상품 정보를 반환합니다.
    });
    return products; // 최종적으로 추출된 상품 정보 배열을 반환합니다.
}
