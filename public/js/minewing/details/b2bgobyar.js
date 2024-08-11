// B2B gobycar Details

/**
 * id : jskorea2024
 * password : tjddlf88!@
 */

const puppeteer = require('puppeteer');
const { signIn } = require('./common.js');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const [tempFilePath, id, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(
            page, id, password,
            "http://b2bgobycar.co.kr/member/login.php",
            "#loginId",
            "#loginPwd",
            ".skinbtn.point2.l-login"
        );
        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (e) {
        console.error(e);
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
                document.querySelector('#frmView > div > div.goods-header > div.top > div > h2');
            const productPrice =
                document.querySelector('#frmView > div > div.item > ul > li.price > div > strong');
            const productImage = 
                document.querySelector('#mainImage > img');
            const productDetailElements =
                document.querySelectorAll('#detail > div.txt-manual > p > img');
            if(productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for(const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if(
                    tempProductDetailSrc === '' ||
                    tempProductDetailSrc === ''
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
        return await page.$$('select');
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value == '')
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