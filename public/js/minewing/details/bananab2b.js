const fs = require('fs');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://bananab2b.shop/login?redirect=/', { waitUntil: 'networkidle0' });
    await page.type('input[class="TextField_input__hOlLE"]', username);
    await page.type('input[type="password"]', password);
    await page.click('#__next > div.PageLayout_layout-container__dJ80A.PageLayout_topBanner__r3_gf.PageLayout_top__9MJc2.PageLayout_header__vbEvv.PageLayout_gnb__Yvsel > div.PageLayout_content__WCS1_ > div > section > div > form > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;
        const productData = await page.evaluate(() => {
            const productName = document.querySelector('.ProductDetail_title-button__ZBnqo > .ProductDetail_title__JikYt').textContent.trim();
            const productPrice = document.querySelector('.Price_price-wrapper__jTdRi').textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('.ProductDetail_thumbnail__KX26C > img').src;
            const productDetailElements = document.querySelectorAll('.ProductDetail_gap__KbFAP > .ProductDetail_content__wenPZ img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                productDetail.push(productDetailElement.src);
            }
            const productData = {
                productName,
                productPrice,
                productImage,
                productDetail
            };
            return productData;
        });
        const { productName, productPrice, productImage, productDetail } = productData;
        const product = {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 56
        };
        return product;
    } catch (error) {
        console.error(error);
        return false;
    }
}
async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('div.ProductDetail_detail-wrapper__bj3TT > div.ProductDetail_option-wrapper__R8tux > table.option-table > tbody > tr > td > div.ProductDetail_option-component-wrapper__ezhW4 > div.ProductDetail_option-dropdown-wrapper__HlQLO > div.Dropdown_dropdown-wrapper__YX4fj.ProductDetail_option-dropdown__MEg1Q.Dropdown_L__aCX5e');
    }
    async function selectOption(page, $option) {
        await $option.click();
        await page.waitForSelector('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div > span');
    }
    async function processSelectOptions(page, productOptions = []) {
        const optionElements = await reloadSelects();
        if (optionElements.length === 1) {
            // 옵션이 1개일 때
            await selectOption(page, optionElements[0]);
            const options = await page.evaluate(() => {
                const optionElements = document.querySelectorAll('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div');
                return Array.from(optionElements)
                    .map(option => {
                        const optionText = option.querySelector('span:nth-of-type(1)').textContent.trim();
                        const isSoldOut = option.querySelector('span:nth-of-type(2)') ? option.querySelector('span:nth-of-type(2)').textContent.includes('품절') : false;
                        return { optionText, isSoldOut };
                    })
                    .filter(({ optionText, isSoldOut }) => !isSoldOut); // '품절'이 아닌 텍스트만 반환
            });

            for (let j = 0; j < options.length; j++) {
                let { optionText } = options[j];
                const optionPriceMatch = optionText.match(/[\+\-]([\d,]+)원/);
                const optionPrice = optionPriceMatch ? parseInt(optionPriceMatch[1].replace(/,/g, ''), 10) : 0;
                optionText = optionText.replace(/\([\+\-]([\d,]+)원\)/, '').replace(/\(.*?\)/, '').trim();
                productOptions.push({ optionName: optionText, optionPrice });
            }
        } else {
            // 옵션이 여러 개일 때
            for (let i = 0; i < optionElements.length - 1; i++) {
                // 첫 번째 옵션 선택
                await selectOption(page, optionElements[i]);
                const firstOptions = await page.evaluate(() => {
                    const optionElements = document.querySelectorAll('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div');
                    return Array.from(optionElements)
                        .map(option => {
                            const optionText = option.querySelector('span:nth-of-type(1)').textContent.trim();
                            const isSoldOut = option.querySelector('span:nth-of-type(2)') ? option.querySelector('span:nth-of-type(2)').textContent.includes('품절') : false;
                            return { optionText, isSoldOut };
                        })
                        .filter(({ optionText, isSoldOut }) => !isSoldOut); // '품절'이 아닌 텍스트만 반환
                });

                for (let j = 0; j < firstOptions.length; j++) {
                    const { optionText: firstOptionText, isSoldOut: firstIsSoldOut } = firstOptions[j];
                    if (firstIsSoldOut) continue; // '품절' 옵션 건너뛰기

                    // 첫 번째 옵션의 요소 선택
                    await selectOption(page, optionElements[i]);
                    await page.evaluate((index) => {
                        document.querySelectorAll('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div')[index].click();
                    }, j);

                    // 두 번째 옵션 선택 및 항목 추출
                    await selectOption(page, optionElements[i + 1]);
                    const secondOptions = await page.evaluate(() => {
                        const optionElements = document.querySelectorAll('.ListLayer_item-wrapper__vX37G.ListLayer_neutrals__72o0I > span > div');
                        return Array.from(optionElements)
                            .map(option => {
                                const optionText = option.querySelector('span:nth-of-type(1)').textContent.trim();
                                const isSoldOut = option.querySelector('span:nth-of-type(2)') ? option.querySelector('span:nth-of-type(2)').textContent.includes('품절') : false;
                                return { optionText, isSoldOut };
                            })
                            .filter(({ optionText, isSoldOut }) => !isSoldOut); // '품절'이 아닌 텍스트만 반환
                    });

                    for (let l = 0; l < secondOptions.length; l++) {
                        const { optionText: secondOptionText, isSoldOut: secondIsSoldOut } = secondOptions[l];
                        if (secondIsSoldOut) continue; // '품절' 옵션 건너뛰기

                        let optionName = `${firstOptionText} ${secondOptionText}`;
                        const optionPriceMatch = optionName.match(/[\+\-]([\d,]+)원/);
                        const optionPrice = optionPriceMatch ? parseInt(optionPriceMatch[1].replace(/,/g, ''), 10) : 0;
                        optionName = optionName.replace(/\([\+\-]([\d,]+)원\)/, '').replace(/\(.*?\)/, '').trim(); // 추가 정보를 제거
                        productOptions.push({ optionName, optionPrice });
                    }

                    // 비어있는 부분 클릭하여 드롭다운 닫기
                    await page.evaluate(() => {
                        document.querySelector('body').click();
                    });
                }
            }
        }
        return productOptions;
    }



    let selects = await reloadSelects();
    if (selects.length < 1) {
        return {
            hasOption: false,
            productOptions: []
        };
    }
    const productOptions = await processSelectOptions(page);
    return {
        hasOption: true,
        productOptions: productOptions
    };
}
