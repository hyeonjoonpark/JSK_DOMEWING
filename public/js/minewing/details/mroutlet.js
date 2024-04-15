const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const urls = ['https://mroutlet.cafe24.com/product/detail.html?product_no=52&cate_no=24&display_group=1#none'];
        // const username = "sungiltradekorea";
        // const password = "tjddlf88!@";
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
    try {
        const productImage = await getProductImage(page);
        if (productImage.includes('div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > img')) {
            return false;
        }
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption ? await getProductOptions(page) : [];
        const productPrice = await page.evaluate(() => {
            const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });
        const productDetail = await getProductDetail(page);
        if (productDetail === false) {
            return false;
        }
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 58
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}

async function getProductDetail(page) {
    return await page.evaluate(async () => {
        const distance = 200;
        const scrollInterval = 155;
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

        // 이미지 URL 추출 로직
        const productDetailImageElements = document.querySelectorAll('#prdDetail > div > p img');
        const excludedPaths = ['/web/img/start', '/web/img/event'];
        const productImages = [...productDetailImageElements]
            .filter(img => img.src && !excludedPaths.some(path => img.src.includes(path)))
            .map(img => img.src); // 이미 절대 경로이므로 바로 src를 사용

        return productImages;
    });
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
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**' && !opt.text.toLowerCase().includes("품절")) // 품절 텍스트를 대소문자 구분 없이 필터링
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000)); // AJAX 로딩 등 페이지 업데이트 대기
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];

                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([\+\-]?\d{1,3}(,\d{3})*원\)/g, "").trim() // 가격 정보 제거
                    ).join(", ");

                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/\(([\+\-]?\d{1,3}(,\d{3})*원)\)/);
                        return total + (matches ? parseInt(matches[1].replace(/,|원|\+/g, ''), 10) : 0); // 가격 정보 계산
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
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.headingArea > h2');
        let productNameText = productNameElement.textContent.trim();
        productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '');
        return productNameText.trim();
    });

    return productName;
}

async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.ProductOption0');
        const filteredSelectElements = Array.from(selectElements).filter(select => select.name !== 'addproduct_option_id_774_1');
        if (filteredSelectElements.length > 0) {
            return true;
        }
        return false;
    });
}



