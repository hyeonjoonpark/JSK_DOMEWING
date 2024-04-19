const fs = require('fs');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
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
    await page.goto('https://www.ibiskorea.com/shop/member.html?type=login', { waitUntil: 'networkidle0' });
    await page.type('#loginWrap > div > div > div.mlog > form > fieldset > ul > li.id > input', username);
    await page.type('#loginWrap > div > div > div.mlog > form > fieldset > ul > li.pwd > input', password);
    await page.click('#loginWrap > div > div > div.mlog > form > fieldset > a > img');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function checkImageUrl(page, url) {
    try {
        // 이미지 URL에 대해 HEAD 요청을 수행
        const response = await page.goto(url, { method: 'HEAD' });
        // 200~299 상태 코드는 성공을 의미
        return response.status() >= 200 && response.status() < 300;
    } catch (error) {
        console.error(`Error checking image URL: ${url}`, error);
        return false;
    }
}
function normalizeUrl(url) {
    return url.trim().replace(/^https?:\/\//, '').replace(/\/$/, '');
}

const skipUrls = [
    "gi.esmplus.com/ibis001/info/TOP/t002.jpg",
    "gi.esmplus.com/ibis001/info/TOP/t001.jpg",
    "gi.esmplus.com/ibis001/info/TOP/t003.jpg",
    "gi.esmplus.com/ibis001/ibis_detail/story.jpg",
    "gi.esmplus.com/ibis001/info/END/b001.jpg",
    "gi.esmplus.com/ibis001/info/END/b002.jpg",
    "gi.esmplus.com/ibis001/info/END/b003.jpg"
].map(normalizeUrl);


async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });

        const isSelectorPresent = await page.evaluate(() => {
            return !!document.querySelector('#form1 > div > div.prd-btns > div:nth-child(1)');
        });

        if (isSelectorPresent) {
            return false;
        }

        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;

        const productData = await page.evaluate(() => {
            const productName = document.querySelector('#form1 > div > h3').textContent.trim();
            const priceElements = document.querySelectorAll('table > tbody > tr > td.price > div.tb-left');//

            if (priceElements.length < 2) { // 2번째 요소가 없으면 버린다는거임
                return false;
            }

            // 가격 정보를 숫자로 변환
            const rawPrice = priceElements[1].textContent.trim().replace(/[^\d]/g, '');
            const productPrice = parseInt(rawPrice, 10);

            // 가격 정보가 0이거나 숫자 변환이 실패한 경우 (NaN)
            if (!productPrice) {
                return false; // false 반환하고 처리 중단
            }

            const productImage = document.querySelector('#lens_img').src;
            const productDetailElements = document.querySelectorAll('#productDetail > div > div.prd-detail > p > img');

            // 필요한 데이터 반환
            return {
                productName,
                productPrice: rawPrice, // 문자열 가격을 반환
                productImage,
                productDetailElements: Array.from(productDetailElements).map(el => el.src)
            };
        });


        // 이미지 URL 검증
        const validProductDetails = [];
        for (const src of productData.productDetailElements) {
            const normalizedSrc = normalizeUrl(src);
            if (skipUrls.includes(normalizedSrc)) {
                continue; // URL이 건너뛰기 목록에 있으면 건너뜁니다.
            }
            if (await checkImageUrl(page, src)) {
                validProductDetails.push(src);
            }
        }

        const product = {
            ...productData,
            productDetail: validProductDetails,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 53
        };

        return product;
    } catch (error) {
        console.error(error);
        return false;
    }
}

async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('select.basic_option');
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '옵션 선택' && !opt.text.includes('품절'))
            );
            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];
                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = "";
                    newSelectedOptions.forEach(opt => {
                        let optText = opt.text;
                        optionName = optionName.length > 0 ? `${optionName} / ${optText}` : optText;
                    });
                    const optionPriceMatch = optionName.match(/\(([\d,]+)원\)/);
                    const optionPrice = parseInt(optionPriceMatch ? optionPriceMatch[1].replace(/,/g, '') : "0");
                    optionName = optionName.replace(/\(([\d,]+)원\)/, '').trim();
                    const productOption = { optionName, optionPrice };
                    productOptions.push(productOption);
                }
                selects = await reloadSelects();
                if (currentDepth > 0) {
                    await reselectOptions(selects, selectedOptions);
                    selects = await reloadSelects();
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
    const productOptions = await processSelectOptions(selects);
    return {
        hasOption: true,
        productOptions: productOptions
    };
}
