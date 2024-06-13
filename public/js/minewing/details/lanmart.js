const fs = require('fs');
const puppeteer = require('puppeteer');

(async () => {
    const [tempFilePath, username, password] = process.argv.slice(2);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        await signIn(page, username, password);
        const products = [];

        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product) {
                products.push(product);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

async function signIn(page, username, password) {
    await page.goto('https://www.lanmart.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(1) > input', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td > input', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input');
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        await scrollToDetail(page);

        const productDetail = await page.evaluate(() => {
            const excludedImageUrls = [
                "http://www.lanmart.co.kr/shop/data/sphtml_button.png",
                "http://www.lanmart.co.kr/shop/data/sphtml_button2.png",
                "http://www.lanmart.co.kr/shop/product/Product_notice860.jpg",
                "http://www.kwshop.co.kr/contents/FAQ/FAQ_top.jpg",
                "http://www.kwshop.co.kr/ariel/USB/KW-825/KW-825_01.jpg",
                "http://ai.esmplus.com/ssarang0212/2020-02-02/MBF_img.gif",
            ];
            const productDetailElements = Array.from(document.querySelectorAll('#contents img')).filter(el => !excludedImageUrls.includes(el.src));
            if (productDetailElements.length === 0) return false;
            return productDetailElements.map(el => el.src);
        });

        if (!productDetail) {
            return false; // 제외된 이미지 후 남은 상세 이미지가 없으면 처리 중단
        }

        const productImage = await page.evaluate(() => document.querySelector('#objImg')?.src || false);
        if (!productImage) return false;

        const productName = await page.evaluate(() => {
            const nameElement = document.querySelector('#goods_spec > form > div.price > table > tbody > tr:nth-child(1) > td > span');
            return nameElement ? nameElement.textContent.trim().replace(/\[.*?\]/g, '').replace(/\(.*?\)/g, '') : false;
        });
        if (!productName) return false;

        const productPrice = await page.evaluate(() => {
            const priceElement = document.querySelector('[id="price"]');
            const priceText = priceElement ? priceElement.textContent.trim().replace(/[^\d]/g, '') : '0';
            return parseInt(priceText, 10);
        });
        if (isNaN(productPrice) || productPrice < 1) return false;

        const { hasOption, productOptions } = await getProductOptions(page);

        return {
            productName,
            productPrice,
            productImage,
            productDetail,
            productHref: url,
            sellerID: 63,
            hasOption,
            productOptions
        };
    } catch (error) {
        console.error('Error in scrapeProduct:', error);
        return false;
    }
}

async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('#goods_spec > form > table.top.mt_0 > tbody > tr > td > div > select option');
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
                opts.slice(1).map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '' && opt.value !== '-1')
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];
                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt => opt.text).join(' / ').replace(/\(([\d,]+)원\)/, '').trim();
                    const optionPriceMatch = optionName.match(/\(([\d,]+)원\)/);
                    const optionPrice = parseInt(optionPriceMatch ? optionPriceMatch[1].replace(/,/g, '') : '0');
                    productOptions.push({ optionName, optionPrice });
                }
                selects = await reloadSelects();
                if (currentDepth > 0) {
                    await reselectOptions(selects, selectedOptions);
                }
            }
        }
        return productOptions;
    }

    let selects = await reloadSelects();
    if (selects.length < 1) {
        return { hasOption: false, productOptions: [] };
    }
    const productOptions = await processSelectOptions(selects);
    return { hasOption: true, productOptions };
}

async function scrollToDetail(page) {
    await page.evaluate(async () => {
        const distance = 50;
        const scrollInterval = 100; // 스크롤 동작 사이의 대기 시간 (밀리초)
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.querySelector('#content > div.indiv > div.goodsInfo > div:nth-child(2) > table > tbody > tr');
            const prdInfoElement = document.querySelector('div.delivery_wrap.clear');
            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                    await new Promise(resolve => setTimeout(resolve, scrollInterval));
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                break;
            } else {
                window.scrollBy(0, distance);
                await new Promise(resolve => setTimeout(resolve, scrollInterval));
            }
        }
    });
}
