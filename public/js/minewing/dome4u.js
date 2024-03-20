const puppeteer = require('puppeteer');

// 비동기 함수를 사용하여 스크립트의 주 실행 흐름을 정의합니다.
(async () => {
    // Puppeteer를 사용하여 브라우저 인스턴스를 생성합니다. headless 모드를 false로 설정하여 GUI를 사용합니다.
    const browser = await puppeteer.launch({ headless: false });
    // 새 탭 페이지를 엽니다.
    const page = await browser.newPage();
    // 커맨드 라인에서 입력된 인자들을 배열로 가져옵니다. 첫 두 인자는 무시합니다.
    const [listURL, username, password] = process.argv.slice(2);

    try {
        // 로그인 함수를 호출하여 사용자 인증을 수행합니다.
        await login(page, username, password);
        // 지정된 URL에서 상품 목록 페이지 처리를 수행합니다.
        await processPage(page, listURL);
        // 상품 데이터를 스크래핑합니다.
        const products = await scrapeProducts(page);
        // 스크래핑된 상품 데이터를 JSON 형식으로 콘솔에 출력합니다.
        console.log(JSON.stringify(products));
    } catch (error) {
        // 오류가 발생한 경우, 오류 메시지를 콘솔에 출력합니다.
        console.error(error);
    } finally {
        // 작업이 완료되면 브라우저를 닫습니다.
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://dome4u.co.kr/home/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#id', username);
    await page.type('#passwd', password);
    await page.click('#login_submit');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function processPage(page, listURL) {
    await page.goto(listURL);
    await page.waitForSelector('#contents > div.conInfo.clearfix > em');
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.conInfo.clearfix > em').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    const url = new URL(listURL);
    const params = new URLSearchParams(url.search);
    const cate = params.get('cate');
    const newUrl = "https://dome4u.co.kr/home/product/?brand=&sortby=viewcount&ctl=m&seller_id=&prttp=&brandname=&brandsearchcate1=&brandsearchcate2=&glimit=&dp_view_type=&brandcategory=&cate=" + cate + "&search_f=&search_word=&cou=&pon=&delv1=&delv2=&delv3=&delv4=&stprice=&edprice=&rsearch_word=&view_type=0&page=1&sortby=viewcount&glimit=&view_type=1&glimit=" + numProducts;
    await page.goto(newUrl, { waitUntil: 'domcontentloaded', timeout: 300000 });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('#tab111 > div dl');
        const products = [];

        function processProduct(productElement) {
            const productNameElement = productElement.querySelector('#tab111 > div> dl > dd.pName > a');
            const name = productNameElement.textContent.trim();
            const productPriceText = productElement.querySelector('#tab111 > div > dl > dd.price.type2').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const imageElement = productElement.querySelector('#tab111 > div > dl > dd.imgArea > a > img');
            const image = imageElement.src;
            const href = productElement.querySelector('#tab111 > div > dl > dd.imgArea > a').href.trim();
            const platform = '도매포유';

            return { name, price, image, href, platform };
        }
        function hasStockByText(productElement) {
            // 상품 요소 내의 모든 텍스트 노드를 검사하여 '품절' 텍스트가 있는지 확인
            const textContent = productElement.textContent || productElement.innerText;
            return !textContent.includes('품절');
        }

        for (const productElement of productElements) {
            if (hasStockByText(productElement)) {
                try {
                    const productInfo = processProduct(productElement);
                    products.push(productInfo);
                } catch (error) {
                    console.error("Error processing product: ", error);
                }
            }
        }

        return products;
    });

    return products;
}



//         function hasStockMethod(productElement) {
//             const soldOutImage = productElement.querySelector('li > div > div.txt > div > img');
//             return !soldOutImage;
//         }

//         for (const productElement of productElements) {
//             const hasStockMethodResult = hasStockMethod(productElement);
//             if (hasStockMethodResult === false) {
//                 continue;
//             }
//             try {
//                 const productInfo = processProduct(productElement);
//                 products.push(productInfo);
//             } catch (error) {
//                 console.error("Error processing product: ", error);
//                 continue;
//             }
//         }

//         return products;
//     });

//     return products;
// }

