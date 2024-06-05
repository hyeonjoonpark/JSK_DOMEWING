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
            await page.goto(url, { waitUntil: 'domcontentloaded' });
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

async function signIn(page, username, password) {
    await page.goto('https://mroutlet.cafe24.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await scrollToDetail(page);
    try {
        const optionCount = await getOptionCount(page);

        // 옵션이 없는 경우
        if (optionCount === 0) {
            const productDetailImages = await getProductDetail(page);
            if (productDetailImages.length === 0) {  // 상세 이미지가 하나도 없는 경우
                return false;  // 해당 제품은 스크래핑하지 않음
            }

            const productImage = await getProductImage(page);
            const productName = await getProductName(page);
            const productPrice = await page.evaluate(() => {
                const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
                return productPrice;
            });

            const product = {
                productName: productName,
                productPrice: productPrice,
                productImage: productImage,
                productDetail: productDetailImages,
                hasOption: false,  // 옵션이 없음
                productOptions: [],  // 옵션 없음
                productHref: productHref,
                sellerID: 58
            };
            return product;
        }

        // 옵션이 있는 경우
        const productOptions = await getProductOptions(page);
        if (productOptions.length === 0) {  // 모든 옵션이 필터링되어 없으면
            return false;  // 해당 제품은 품절이므로 스크래핑하지 않음
        }

        const productDetailImages = await getProductDetail(page);
        if (productDetailImages.length === 0) {  // 상세 이미지가 하나도 없는 경우
            return false;  // 해당 제품은 스크래핑하지 않음
        }

        const productImage = await getProductImage(page);
        const productName = await getProductName(page);
        const productPrice = await page.evaluate(() => {
            const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });

        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetailImages,
            hasOption: true,  // 옵션이 있음
            productOptions: productOptions,  // 필터링된 옵션 목록
            productHref: productHref,
            sellerID: 58
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}

async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > img').src;
    });
    return productImage;
}

async function getProductOptions(page) {
    async function reloadSelects() {
        const selectHandles = await page.$$('select.ProductOption0'); // 모든 select.ProductOption0 요소를 선택
        const filteredHandles = [];

        for (const handle of selectHandles) {
            const name = await page.evaluate(el => el.name, handle); // 각 요소의 name 속성 가져오기
            if (name !== 'addproduct_option_id_774_1') { // 조건에 맞지 않는 이름 필터링
                filteredHandles.push(handle);
            }
        }

        return filteredHandles; // 필터링된 ElementHandle 배열 반환
    }

    async function processSelectOptions(selects, currentDepth = 0, selectedOptions = [], productOptions = []) {
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text }))
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**' && !opt.text.toLowerCase().includes("품절")) // 품절 텍스트를 대소문자 구분 없이 필터링
            );

            if (options.length === 0) {
                return false; // 현재 선택 가능한 옵션이 없으면 false 반환
            }

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000)); // AJAX 로딩 등 페이지 업데이트 대기
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];

                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    const result = await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                    if (result === false) {
                        return false; // 하위 선택에서도 모두 품절인 경우 false 반환
                    }
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([\+\-]?\d{1,3}(,\d{3})*원\)/g, "").trim() // 가격 정보 제거
                    ).join(", ");

                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/\(([\+\-]?\d{1,3}(,\d{3})*원)\)/);
                        return total + (matches ? parseInt(matches[1].replace(/,|원|\+/g, ''), 10) : 0); // 가격 정보 계산
                    }, 0);

                    // 옵션명이 없는 경우 상품 건너뛰기
                    if (optionName.trim() !== '') {
                        productOptions.push({ optionName, optionPrice });
                    }
                }
            }
        }
        return productOptions;
    }

    const selects = await reloadSelects();
    const result = await processSelectOptions(selects);
    return result === false ? [] : result; // 결과가 false이면 빈 배열 반환
}

async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.headingArea > h2');
        let productNameText = productNameElement.textContent.trim();
        productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '');
        return productNameText.trim();
    });

    return productName;
}

async function getProductDetail(page) {
    return page.evaluate(() => {
        const productDetailImageElements = document.querySelectorAll('#prdDetail > div > p img');
        const excludedPaths = ['/web/img/start', '/web/img/event'];
        const productImages = [...productDetailImageElements]
            .filter(img => img.src && !excludedPaths.some(path => img.src.includes(path)))
            .map(img => img.src);

        return productImages;
    });
};

async function getOptionCount(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.ProductOption0');
        return selectElements.length;  // 옵션의 개수를 반환
    });
}

async function scrollToDetail(page) {
    await page.evaluate(async () => {
        const distance = 50;
        const scrollInterval = 5;
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
}
