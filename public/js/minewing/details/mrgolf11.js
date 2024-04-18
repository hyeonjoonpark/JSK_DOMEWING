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
    await page.goto('https://mrgolf11.cafe24.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > fieldset > div.login__button > a.btnSubmit.gFull.sizeL');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productOptionData = await getProductOptions(page);
        await page.waitForSelector('#span_product_price_text');  // 가격 정보가 업데이트 될 때까지 기다림

        const productData = await page.evaluate(() => {
            const productNameElement = document.querySelector('div.infoArea > div.headingArea.sale_on > h1');
            if (!productNameElement) {
                return false; // 상품 이름 요소가 없으면 함수를 즉시 종료하고 false 반환
            }
            const productName = productNameElement.textContent.trim();

            let productPrice = '';
            const priceElement = document.querySelector('#span_product_price_text');
            if (priceElement) {
                const childDiv = priceElement.querySelector('div');
                productPrice = childDiv ? priceElement.childNodes[0].textContent.trim().replace(/[^\d]/g, '') : priceElement.textContent.trim().replace(/[^\d]/g, '');
            }

            const productImageElement = document.querySelector('div.prdImg > div > a > img');
            if (!productImage) {
                return false;  // 상품 이미지가 없으면 반환 중단
            }
            const productImage = productImageElement.src();

            const productDetailElements = document.querySelectorAll('#prdDetail img');
            const productDetail = [];
            if (productDetailElements.length === 0) {
                return false;  // 상세 이미지가 없으면 반환 중단
            }
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if (!tempProductDetailSrc.includes('open_end.jpg') && !tempProductDetailSrc.includes('open_notice.jpg')) {
                    productDetail.push(tempProductDetailSrc);
                }
            }

            return {
                productName,
                productPrice,
                productImage,
                productDetail
            };
        });

        if (!productData) {
            return false;  // productData가 false이면 함수에서 더 이상 처리하지 않고 중단
        }

        return {
            ...productData,
            hasOption: productOptionData.hasOption,
            productOptions: productOptionData.productOptions,
            productHref: url,
            sellerID: 59
        };
    } catch (error) {
        console.error(error);
        return false;
    }
}

async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('table select');
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**')
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
