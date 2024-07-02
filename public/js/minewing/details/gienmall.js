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
    await page.goto('https://gienmall.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a > img');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        await scrollToDetail(page);
        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;
        const productData = await page.evaluate(() => {
            const productNameElem = document.querySelector('div.xans-element-.xans-product.xans-product-detaildesign > table > tbody > tr:nth-child(1) > td > span');
            const productPriceElem = document.querySelector('#span_product_price_text');
            const productImageElem = document.querySelector('div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > a > img');
            const productDetailElements = document.querySelectorAll('#prdDetail > div img');

            if (!productNameElem || !productPriceElem || !productImageElem || productDetailElements.length < 1) {
                return false;
            }

            let productName = productNameElem.textContent.trim();
            productName = productName.replace(/\[.*?\]/g, '').replace('도매-', '').trim();
            const productPrice = productPriceElem.textContent.trim().replace(/[^\d]/g, '');
            const productImage = productImageElem.src;
            const productDetail = Array.from(productDetailElements)
                .map(el => el.src)
                .filter(src => src !== 'http://buzz71.godohosting.com/start/common/open_end.jpg' && src !== 'http://buzz71.godohosting.com/start/common/open_notice.jpg');

            const productData = {
                productName,
                productPrice,
                productImage,
                productDetail
            };
            return productData;
        });

        if (!productData) {
            return false;
        }

        const { productName, productPrice, productImage, productDetail } = productData;
        const product = {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 75
        };
        return product;
    } catch (error) {
        console.error(error);
        return false;
    }
}

async function getProductOptions(page) {
    async function reloadSelects() {
        const selects = await page.$$('table select');
        return selects.slice(1); // 첫 번째 select를 건너뛰고 나머지를 반환
    }

    async function processSelectOptions(selects, currentDepth = 0, selectedOptions = [], productOptions = []) {
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text }))
                    .filter(opt => opt.value !== '*' && opt.value !== '**' && !opt.text.includes('[품절]'))
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
            }
        }
        return productOptions;
    }

    let selects = await reloadSelects();
    if (selects.length < 1) { // 최소 하나의 셀렉터가 있어야 함 (첫 번째는 건너뛰므로 최소 두 개 있어야 원래 한 개 있는 것과 동일)
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

async function scrollToDetail(page) {
    await page.evaluate(async () => {
        const distance = 45; // 스크롤 이동 거리
        const scrollInterval = 50; // 스크롤 간격
        const maxScrollAttempts = 100; // 최대 스크롤 시도 횟수
        let scrollAttempts = 0;

        while (scrollAttempts < maxScrollAttempts) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.querySelector('#prdDetail > div');
            const prdInfoElement = document.querySelector('#prdInfo > ul');

            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                break;
            } else {
                window.scrollBy(0, distance);
            }

            scrollAttempts++;
            await new Promise(resolve => setTimeout(resolve, scrollInterval));
        }
    });
}
