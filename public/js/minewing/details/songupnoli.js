const fs = require('fs');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const urls = ['https://www.soggupnoli.com/goods/goods_view.php?goodsNo=1000001979'];
        // const username = "jskorea2023";
        // const password = "tjddlf88!@";

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
    await page.goto('https://soggupnoli.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.member_login_box > div.login_input_sec > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;
        const productData = await page.evaluate(() => {
            const productName = document.querySelector('div.item_detail_tit > h3').textContent.trim();
            const productPrice = document.querySelector('dl.item_price > dd > strong > strong').textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('#mainImage > img').src;
            const productDetailElements = document.querySelectorAll('#detail > div.detail_cont > div > div.txt-manual > div img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if (tempProductDetailSrc === 'http://buzz71.godohosting.com/start/common/open_end.jpg' || tempProductDetailSrc === 'http://buzz71.godohosting.com/start/common/open_notice.jpg') {
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
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 87
        };
        return product;
    } catch (error) {
        console.error(error);
        return false;
    }
}

async function getProductOptions(page) {
    try {
        // Ensure hidden select elements are visible
        await page.evaluate(() => {
            document.querySelectorAll('select').forEach(select => {
                select.style.display = 'block';
            });
        });

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
    } catch (error) {
        console.error('Error getting product options:', error);
        return false;
    }
}
