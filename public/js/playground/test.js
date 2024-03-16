const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

        await login(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            if (product !== false) {
                products.push(product);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://gtgb2b.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await scrapeProductPrice(page);
        const hasOption = await getHasOption(page);
        let productOptions = [];
        if (hasOption === true) {
            productOptions = await getProductOptions(page);
        }
        await page.evaluate(() => {
            const productImageThumbElements = document.querySelectorAll('#content > div.goods-view > div.goods > div > div.more-thumbnail > div.slide > div > div > div a');
            productImageThumbElements[productImageThumbElements.length - 1].click();
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        const product = await page.evaluate((productHref, productOptions, hasOption, productPrice) => {
            const productNameElement = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2');
            const productName = productNameElement.textContent.trim();
            const productImage = document.querySelector('#mainImage > img').src;
            const productDetailElements = document.querySelectorAll('#detail > div.txt-manual > div:nth-child(12) > img');
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const productDetailImage = productDetailElement.src;
                productDetail.push(productDetailImage);
            }
            return {
                productName,
                productPrice,
                productImage,
                productDetail,
                hasOption,
                productOptions,
                productHref,
                sellerID: 29
            };
        }, productHref, productOptions, hasOption, productPrice);
        return product;
    } catch (error) {
        return false;
    }
}

async function scrapeProductPrice(page) {
    const productPriceText = await page.evaluate(() => {
        const priceElement = document.querySelector('#frmView > div > div.item > ul > li.price > div > strong');
        return priceElement ? priceElement.textContent.trim() : '';
    });
    return parseInt(productPriceText.replace(/[^\d]/g, ''), 10);
}
async function getHasOption(page) {
    const optionCount = await page.evaluate(() => {
        return document.querySelectorAll('#frmView > div > div.choice > div > div > div > div > ul > li').length;
    });
    return optionCount > 0;
}
async function scrapeFinalPrice(page) {
    // 할인 요소 존재 여부 확인
    const hasDiscount = await page.evaluate(() => {
        const discountElement = document.querySelector('#frmView > div > div.item > ul > li.benefits > div > p.sale > strong');
        return Boolean(discountElement);
    });

    if (hasDiscount) {
        // 할인이 적용된 최종 가격 가져오기
        const finalPriceText = await page.evaluate(() => {
            const finalPriceElement = document.querySelector('div.end-price > ul > li.total > strong');
            return finalPriceElement ? finalPriceElement.textContent.trim() : '';
        });
        // 숫자 외의 모든 문자를 제거하고 숫자만 반환
        return parseInt(finalPriceText.replace(/[^\d]/g, ''), 10);
    } else {
        // 할인 요소가 없는 경우 기본 가격 로직을 사용하거나, 다른 처리를 할 수 있습니다.
        // 여기서는 기본 가격 로직 호출 예시를 넣겠습니다.
        return await scrapeProductPrice(page);
    }
}
async function scrapeProductOptionsWithPrices(page) {
    const optionsData = [];

    // 옵션 목록 가져오기
    const optionsCount = await page.evaluate(() => {
        const optionElements = document.querySelectorAll('#frmView > div > div.choice > div > div > div > div > ul li');
        return optionElements.length;
    });

    // 첫 번째 옵션은 건너뛰고, 두 번째 옵션부터 처리
    for (let i = 1; i < optionsCount; i++) {
        // 옵션 클릭
        await page.evaluate((index) => {
            document.querySelectorAll('#frmView > div > div.choice > div > div > div > div > ul li')[index].click();
        }, i);

        // 최종 가격 및 옵션명 추출
        const optionData = await page.evaluate(() => {
            const optionNameElement = document.querySelector('#frmView > div > div.choice > div > div > div > div > ul > li.selected');
            let optionName = optionNameElement ? optionNameElement.textContent.trim() : '';
            // ':' 이후의 텍스트 제거
            optionName = optionName.split(':')[0].trim();

            const finalPriceElement = document.querySelector('div.end-price > ul > li.total > strong');
            const finalPrice = finalPriceElement ? finalPriceElement.textContent.trim().replace(/[^\d]/g, '') : '';

            return { optionName, finalPrice: parseInt(finalPrice, 10) };
        });

        optionsData.push(optionData);

        // 선택한 옵션 취소
        if (i < optionsCount - 1) { // 마지막 옵션은 취소할 필요 없음
            await page.click(`#option_display_item_441_1710467767243 > div > div.del > button`);
            // 취소 후 다음 옵션 선택을 위해 잠시 대기 (필요에 따라 조절)
            await page.waitForTimeout(1000);
        }
    }

    return optionsData;
}
async function scrapeOptionsAndPricesForTwoOptionProducts(page) {
    const combinedOptionsData = [];

    // 옵션 그룹을 식별하기 위한 셀렉터를 실제 페이지에 맞게 조정해야 합니다.
    const firstOptionSelector = '#firstOptionSelector';
    const secondOptionSelector = '#secondOptionSelector';
    const priceSelector = 'div.end-price > ul > li.total > strong'; // 최종 가격 셀렉터

    // 첫 번째 옵션 그룹의 옵션 값들을 가져옵니다.
    const firstOptions = await page.$$eval(`${firstOptionSelector} > option:not(:first-child)`, options => options.map(option => option.value));

    // 두 번째 옵션 그룹의 옵션 값들을 가져옵니다.
    const secondOptions = await page.$$eval(`${secondOptionSelector} > option:not(:first-child)`, options => options.map(option => option.value));

    for (const firstOptionValue of firstOptions) {
        await page.select(firstOptionSelector, firstOptionValue);
        await page.waitForTimeout(1000); // 페이지가 옵션 선택을 반영하여 업데이트되기를 기다립니다.

        for (const secondOptionValue of secondOptions) {
            await page.select(secondOptionSelector, secondOptionValue);
            await page.waitForTimeout(1000); // 페이지가 두 번째 옵션 선택을 반영하기를 기다립니다.

            // 현재 선택된 옵션 조합에 따른 최종 가격을 추출합니다.
            const finalPrice = await page.$eval(priceSelector, el => el.textContent.trim());

            // 선택된 옵션명을 가져옵니다 (필요에 따라 적용).
            const firstOptionText = await page.$eval(`${firstOptionSelector} > option:checked`, option => option.textContent.trim());
            const secondOptionText = await page.$eval(`${secondOptionSelector} > option:checked`, option => option.textContent.trim());

            combinedOptionsData.push({
                firstOption: firstOptionText,
                secondOption: secondOptionText,
                price: finalPrice
            });
        }
    }

    return combinedOptionsData;
}

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.goto('여기에_상품_페이지_URL_입력'); // 실제 상품 페이지 URL로 변경

    const optionsAndPrices = await scrapeOptionsAndPricesForTwoOptionProducts(page);
    console.log(optionsAndPrices);

    await browser.close();
})();
