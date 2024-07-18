const fs = require('fs');
const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    let cookies = null;
    let localStorageData = null;
    try {
        const urls = ['https://comeonshop.co.kr/product/detail.html?product_no=1742&cate_no=47&display_group=1'];
        const username = "jskorea2023";
        const password = "tjddlf88!@";

        // 로그인 후 쿠키와 로컬 스토리지 저장
        await signIn(page, username, password);
        cookies = await page.cookies();
        localStorageData = await page.evaluate(() => JSON.stringify(localStorage));

        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url, cookies, localStorageData);
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
    await page.goto('https://nanuli2022.cafe24.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('div > div > fieldset > a');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
    // 로그인 후 쿠키와 로컬 스토리지 저장
    const cookies = await page.cookies();
    const localStorageData = await page.evaluate(() => JSON.stringify(localStorage));
    return { cookies, localStorageData };
}

async function scrapeProduct(page, url, cookies, localStorageData) {
    try {
        // 쿠키와 로컬 스토리지를 설정하여 세션 유지
        await page.setCookie(...cookies);
        await page.evaluate((data) => {
            const localStorage = JSON.parse(data);
            for (let key in localStorage) {
                window.localStorage.setItem(key, localStorage[key]);
            }
        }, localStorageData);

        await page.goto(url, { waitUntil: 'networkidle0' });
        await scrollDown(page);

        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;

        const productData = await page.evaluate(() => {
            const productName = document.querySelector('div.detailArea div.buy-scroll-box > h2').textContent.trim();
            const productPrice = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.buy-wrapper > div.buy-scroll-box > div.infoArea.DB_rate > div.xans-element-.xans-product.xans-product-detaildesign > table > tbody > tr.price.xans-record- > td > span > strong').textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('#big_img_box > div.lslide.active > img').src;
            const productDetailElements = document.querySelectorAll('#prdDetail > div.cont img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if (tempProductDetailSrc === 'http://buzz71.godohosting.com/start/common/open_end.jpg' || tempProductDetailSrc === 'http://buzz71.godohosting.com/start/common/open_notice.jpg') {
                    continue;
                }
                productDetail.push(tempProductDetailSrc);
            }
            return {
                productName,
                productPrice,
                productImage,
                productDetail
            };
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
            sellerID: 80
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '-1')
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

const scrollDown = async (page) => {
    await page.evaluate(async () => {
        const distance = 20;
        const scrollInterval = 100;
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
    });
}
