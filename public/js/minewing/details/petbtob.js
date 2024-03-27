const puppeteer = require('puppeteer');
const fs = require('fs');
// 비동기 함수를 시작합니다. 이 함수는 전체 스크립트의 메인 로직을 담당합니다.
(async () => {
    // Puppeteer로 브라우저 인스턴스를 시작합니다. headless 모드를 비활성화하여 브라우저 UI가 보이게 합니다.
    const browser = await puppeteer.launch({ headless: false });
    // 새로운 페이지(탭)을 생성합니다.
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const urls = [''];
        // const username = "jskorea2022";
        // const password = "tjddlf88!@#";
        await signIn(page, username, password);
        // 스크래핑된 제품 정보를 저장할 배열을 초기화합니다.
        const products = [];
        // 읽어들인 URL들을 순회하면서 각 제품 페이지에 접근합니다.
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            products.push(product);
        }
        // 최종적으로 스크래핑된 모든 제품 정보를 콘솔에 출력합니다.
        console.log(JSON.stringify(products));
    } catch (error) {
        // 에러가 발생했을 경우 에러 메시지를 콘솔에 출력합니다.
        console.error('Error occurred:', error);
    } finally {
        // 모든 작업이 완료되면, 브라우저 인스턴스를 닫습니다.
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}
// 페이지와 제품의 링크를 받아 제품의 정보를 크롤링하는 비동기 함수입니다.
async function scrapeProduct(page, productHref) {
    try {
        // 제품 이미지 URL을 가져옵니다.
        const productImage = await getProductImage(page);
        if (productImage.includes('img_product_big.gif')) {
            return false;
        }
        // 제품 상세 설명에서 필요한 이미지 URL들을 추출합니다.
        const productDetail = await getProductDetail(page);
        if (productDetail === false) {
            return false;
        }
        // 제품 이름을 가져옵니다.
        const productName = await getProductName(page);
        // 제품 옵션이 있는지 확인합니다.
        const hasOption = await getHasOption(page);
        // 옵션이 있다면, 옵션 정보를 가져오고, 없다면 빈 배열을 할당합니다.
        const productOptions = hasOption ? await getProductOptions(page) : [];
        // 제품 가격을 가져옵니다. 옵션이 있다면 0을 할당하고, 없으면 페이지에서 가격을 추출합니다.
        const productPrice = await page.evaluate(() => {
            // 페이지에서 제품 가격을 선택하여 텍스트로 가져오고, 숫자가 아닌 문자를 제거합니다.
            const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });
        // 위에서 추출한 정보들로 제품 객체를 구성합니다.
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 38
        };
        // 최종적으로 구성된 제품 객체를 반환합니다.
        return product;
    } catch (error) {
        // 오류가 발생했다면, 콘솔에 오류를 출력하고, false를 반환합니다.
        console.error('Error occurred:', error);
        return false;
    }
}

// 페이지에서 제품의 상세 설명을 크롤링하여 이미지 URL들을 배열로 반환하는 비동기 함수입니다.#prdDetail > div img
async function getProductDetail(page) {
    return await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#prdDetail > div.cont img');
        if (productDetailElements.length > 0) {
            return Array.from(productDetailElements, element => element.src);
        }
        return false;
    });
}

// 페이지에서 주요 제품 이미지의 URL을 추출하는 비동기 함수입니다.
async function getProductImage(page) {
    // 페이지의 DOM을 조사하여 주요 이미지의 src 속성값을 반환합니다.
    const productImage = await page.evaluate(() => {
        return document.querySelector('div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg.item > div.thumbnail > a > img').src;
    });
    // 추출된 이미지 URL을 반환합니다.
    return productImage;
}
async function getProductOptions(page) {
    async function reloadSelects() {
        return page.$$('select.ProductOption0');
    }

    async function resetSelects() {
        const delBtn = await page.$('#option_box1_del');
        if (delBtn) {
            await delBtn.click();
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }

    async function reselectOptions(selects, selectedOptions) {
        for (let i = 0; i < selectedOptions.length; i++) {
            await selects[i].select(selectedOptions[i].value);
            await new Promise(resolve => setTimeout(resolve, 1000));
            if (i < selectedOptions.length - 1) {
                selects = await reloadSelects();
            }
        }
    }

    async function processSelectOptions(selects, currentDepth = 0, selectedOptions = [], productOptions = []) {
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text }))
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**' && !opt.text.includes("품절"))
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];

                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([\+\-]?\d{1,3}(,\d{3})*원\)/g, "").trim()
                    ).join(", ");
                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/\(([\+\-]?\d{1,3}(,\d{3})*원)\)/);
                        return total + (matches ? parseInt(matches[1].replace(/,|원|\+/g, ''), 10) : 0);
                    }, 0);
                    productOptions.push({ optionName, optionPrice });
                }

                await resetSelects();
                selects = await reloadSelects();
                if (currentDepth > 0) {
                    await reselectOptions(selects, selectedOptions);
                    selects = await reloadSelects();
                }
            }
        }
        return productOptions;
    }

    const selects = await reloadSelects();
    return processSelectOptions(selects);
}

// 페이지에서 제품의 이름을 가져오는 비동기 함수입니다.
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h2');
        let productNameText = productNameElement.textContent.trim();

        // "해외배송"을 포함하는 괄호와 내용을 제거합니다.
        productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '');
        productNameText = productNameText.replace(/할인|최대|이내|요망|대량구매|묶음|기준|온라인|판매가|준수/g, '');

        // 최종적으로 정리된 상품명에서 앞뒤 공백을 제거합니다.
        return productNameText.trim();
    });

    return productName;
}

// 페이지에 제품 옵션이 있는지 확인하는 비동기 함수입니다.
async function getHasOption(page) {
    // 페이지의 DOM을 조사하여 선택 가능한 옵션 요소가 있는지 확인합니다.
    return await page.evaluate(() => {
        // 'select.tune' 클래스를 가진 select 요소들을 모두 선택합니다.
        const selectElements = document.querySelectorAll('select.ProductOption0');
        // 선택 요소가 하나 이상 있다면 true를, 그렇지 않으면 false를 반환합니다.
        if (selectElements.length > 0) {
            return true;
        }
        return false;
    });
}
