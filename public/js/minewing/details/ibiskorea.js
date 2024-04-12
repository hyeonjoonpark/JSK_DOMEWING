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
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });

        // 먼저 해당 셀렉터가 존재하는지 확인합니다.
        const isSelectorPresent = await page.evaluate(() => {
            return !!document.querySelector('#form1 > div > div.prd-btns > div:nth-child(1)');
        });

        // 셀렉터가 존재하면 건너뜁니다.
        if (isSelectorPresent) {
            return false;
        }

        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;

        const productData = await page.evaluate(() => {
            const productName = document.querySelector('#form1 > div > h3').textContent.trim();
            // 두 번째 셀렉터 요소를 선택하기 위해 querySelectorAll을 사용하고 인덱스 1을 사용합니다.
            const priceElements = document.querySelectorAll('table > tbody > tr > td.price > div.tb-left');
            const productPrice = priceElements.length > 1 ? priceElements[1].textContent.trim().replace(/[^\d]/g, '') : 'Price not available';
            const productImage = document.querySelector('#lens_img').src;
            const productDetailElements = document.querySelectorAll('#productDetail > div > div.prd-detail > p > img');

            if (productDetailElements.length < 1) {
                return false;
            }


            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                const skipUrls = [
                    "http://gi.esmplus.com/ibis001/info/TOP/t002.jpg",
                    "http://gi.esmplus.com/ibis001/info/TOP/t001.jpg",
                    "http://gi.esmplus.com/ibis001/info/TOP/t003.jpg",
                    "https://gi.esmplus.com/ibis001/ibis_detail/story.jpg",
                    "http://gi.esmplus.com/ibis001/info/END/b001.jpg",
                    "http://gi.esmplus.com/ibis001/info/END/b002.jpg",
                    "http://gi.esmplus.com/ibis001/info/END/b003.jpg"
                ];

                if (!tempProductDetailSrc || skipUrls.includes(tempProductDetailSrc)) {
                    continue; // 이미지 URL이 건너뛰기 목록에 있으면 건너뛴다
                }
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
