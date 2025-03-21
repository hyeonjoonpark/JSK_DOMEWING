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
    await page.goto('https://sapakorea.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        await scrollToDetail(page);
        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;
        const productData = await page.evaluate(() => {
            const productName = document.querySelector('div.xans-element-.xans-product.xans-product-detaildesign > table > tbody > tr:nth-child(1) > td > span').textContent.trim();
            const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('div.keyImg > div > a > img').src;
            const shippingFee = document.querySelector('table > tbody > tr > td > span > span > strong').textContent.trim().replace(/[^\d]/g, '');
            const productDetailElements = document.querySelectorAll('#prdDetail > div > center img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if (tempProductDetailSrc === 'https://www.mteshop.co.kr/sapa_photo/tw/notice/intro_with.jpg' || tempProductDetailSrc === 'https://www.mteshop.co.kr/sapa_photo/tw/notice/delivery.jpg' || tempProductDetailSrc === 'https://www.mteshop.co.kr/sapa_photo/tw/notice/SAPA_notice02.jpg' || tempProductDetailSrc === 'https://www.mteshop.co.kr/sapa_photo/tw/notice/SAPA_notice01.jpg' || tempProductDetailSrc === 'https://www.mteshop.co.kr/sapa_photo/tw/notice/notice.jpg') {
                    continue;
                }
                productDetail.push(productDetailElement.src);
            }
            const productData = {
                productName,
                productPrice,
                productImage,
                shippingFee,
                productDetail,
            };
            return productData;
        });
        const { productName, productPrice, productImage, productDetail, shippingFee } = productData;
        const product = {
            productName,
            productPrice,
            productImage,
            shippingFee,
            productDetail,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 71
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
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '*' && opt.value !== '**')
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

async function scrollToDetail(page) {
    await page.evaluate(async () => {
        const distance = 45; // 스크롤 이동 거리
        const scrollInterval = 50; // 스크롤 간격
        const maxScrollAttempts = 100; // 최대 스크롤 시도 횟수
        let scrollAttempts = 0;

        while (scrollAttempts < maxScrollAttempts) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.querySelector('#prdDetail > div > h3');
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
