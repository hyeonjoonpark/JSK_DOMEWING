// Details

const puppeteer = require('puppeteer');
const { signIn } = require('./common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password, 'https://joowb.com/member/login.html', '#member_id', '#member_passwd', 'div > fieldset > a.btnSubmit.sizeL.df-lang-button-login');
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

async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productOptionData = await getProductOptions(page);
        const hasOptions = productOptionData.hasOptions;
        const productOptions = productOptionData.productOptions;
        const productData = await page.evaluate(() => {
            const productName = 
                document.querySelector('#df-product-detail > div > div.infoArea-wrap > div > div > div.scroll-wrapper.df-detail-fixed-scroll.scrollbar-macosx > div.df-detail-fixed-scroll.scrollbar-macosx.scroll-content > div.headingArea > h2');
            const productPrice =
                document.querySelector('#totalPrice > span > strong > em');
            const productImage = 
                document.querySelector('#df-product-detail > div > div.imgArea-wrap > div > div > div.thumbnail > span > img');
            const productDetailElements =
                document.querySelectorAll('#prdDetail > div.cont > div > img');
            if(productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for(const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if(
                    tempProductDetailSrc === 'https://joowb.com/web/upload/NNEditor/20210415/f207c5667c8587a042f070648c942766.jpg' ||
                    tempProductDetailSrc === 'https://joowb.com/web/upload/NNEditor/20210415/f207c5667c8587a042f070648c942766.jpg'
                ) {
                    continue;
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
            hasOptions,
            productOptions,
            productionHref: url,
            sellerID: 97
        };

        return product;
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
        if(currentDepth < selects.length) {
            //  옵션을 선택해주세요 문구와 -------------- 선 제외한 option만 가져온다
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '*' && opt.value !== '**')
            );

            for(const option of options) {
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
    // 최소주문수량이 1이하인 경우
    if(selects.length < 1) {
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