const puppeteer = require('puppeteer');
const fs = require('fs');
// 비동기 함수를 시작합니다. 이 함수는 전체 스크립트의 메인 로직을 담당합니다.
(async () => {
    // Puppeteer로 브라우저 인스턴스를 시작합니다. headless 모드를 비활성화하여 브라우저 UI가 보이게 합니다.
    const browser = await puppeteer.launch({ headless: true });
    // 새로운 페이지(탭)을 생성합니다.
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const urls = ['https://living9.com/product/%EC%9D%B8%EB%B8%94%EB%A3%B8-%EB%B6%80%EC%B0%A9%EC%8B%9D-304%EC%8A%A4%ED%85%90-%EB%8B%A4%EC%9A%A9%EB%8F%84-%EC%B2%AD%EC%86%8C%EB%8F%84%EA%B5%AC%ED%99%80%EB%8D%94/1781/category/59/display/1/'];
        // const username = "jskorea2022";
        // const password = "Tjddlf88!@#";
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
    await page.goto('https://living9.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('#member_login_module_id > fieldset > div.login__button > a.btnSubmit.gFull.sizeL');
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
            const priceElement = document.querySelector('#span_product_price_text');
            let textContent = '';
            // childNodes를 순회하며 노드 타입이 TEXT_NODE인 경우만 텍스트를 추출합니다.
            priceElement.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    textContent += node.textContent;
                }
            });
            // 추출한 텍스트에서 숫자가 아닌 문자를 제거합니다.
            const productPrice = textContent.trim().replace(/[^\d]/g, '');
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
            sellerID: 37 // 판매자 ID는 상수로 36를 할당합니다.
        };
        // 최종적으로 구성된 제품 객체를 반환합니다.
        return product;
    } catch (error) {
        // 오류가 발생했다면, 콘솔에 오류를 출력하고, false를 반환합니다.
        console.error('Error occurred:', error);
        return false;
    }
}

// 페이지에서 제품의 상세 설명을 크롤링하여 이미지 URL들을 배열로 반환하는 비동기 함수입니다.
async function getProductDetail(page) {
    try {
        await page.evaluate(async () => {
            const distance = 100;
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
        await page.waitForSelector('#prdDetail img', { timeout: 10000 });
        const imageUrls = await page.evaluate(() => {
            const productDetailElements = document.querySelectorAll('#prdDetail img');
            // "cafe24" 문자열을 포함하지 않는 img 요소의 src 속성만을 필터링하여 추출합니다.
            return Array.from(productDetailElements)
                .map(element => element.src)
                .filter(src => !src.includes('cafe24'));
        });

        return imageUrls.length > 0 ? imageUrls : null;
    } catch (error) {
        console.error(`An error occurred while getting product details: ${error.message}`);
        return null;
    }
}
async function getProductDetail(page) {
    try {
        await page.evaluate(async () => {
            const distance = 100;
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
        const imageUrls = await page.evaluate(() => {
            const productDetailElements = document.querySelectorAll('#prdDetail img');
            // "cafe24" 문자열을 포함하지 않으며, "story" 문자열이 들어간 img 요소의 src 속성은 거릅니다.
            return Array.from(productDetailElements)
                .map(element => element.src)
                .filter(src => !src.includes('cafe24') && !src.includes('story') && !src.includes('notice')); // "story"가 포함된 URL도 제외합니다.
        });

        return imageUrls.length > 0 ? imageUrls : null;
    } catch (error) {
        console.error(`An error occurred while getting product details: ${error.message}`);
        return null;
    }
}





// 페이지에서 주요 제품 이미지의 URL을 추출하는 비동기 함수입니다.
async function getProductImage(page) {
    // 페이지의 DOM을 조사하여 주요 이미지의 src 속성값을 반환합니다.
    const productImage = await page.evaluate(() => {
        return document.querySelector('div.xans-element-.xans-product.xans-product-image.imgArea > div.RW > div.prdImg > div > a > img').src;
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
    try {
        // 요소가 로드될 때까지 최대 N초 대기합니다. 시간은 조절할 수 있습니다.
        await page.waitForSelector('#contents > div.xans-element-.xans-product.xans-product-detail.section > div.detailArea > div.infoArea > div > h1', { timeout: 5000 });

        const productName = await page.evaluate(() => {
            const productNameElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail.section > div.detailArea > div.infoArea > div > h1');
            if (productNameElement !== null) {
                let productNameText = productNameElement.textContent.trim();
                productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '').trim();
                return productNameText;
            } else {
                console.error('Product name element not found');
                return null;
            }
        });

        // 결과 검증 및 처리
        if (productName === null || productName === '') {
            throw new Error('Product name not found or is empty');
        }

        return productName;
    } catch (error) {
        console.error(`Error occurred while getting product name: ${error.message}`);
        // 오류 처리를 위해 null 반환 대신, 오류 상황에 대한 처리를 할 수 있습니다.
        // 예를 들어, 오류 로그를 남기거나, 사용자에게 알림을 보낼 수 있습니다.
        return null; // 또는 적절한 오류 처리
    }
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
