const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await login(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'networkidle0' });
            const product = await buildProduct(page, url);
            if (product !== false) {
                products.push(product);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function login(page, username, password) {
    await page.goto('https://gtgb2b.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function buildProduct(page, productHref) {
    try {
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption ? await getProductOptions(page) : [];
        const productPrice = hasOption ? 0 : await page.evaluate(() => {
            const productPrice = document.querySelector('strong.total_price').textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });
        const productImage = await getProductImage(page);
        const productDetail = await getProductDetail(page);
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 29
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}
async function getProductDetail(page) {
    return await page.evaluate(() => {
        const forbiddenSrces = ['dc_2in1sunshade_01.jpg', '860_GTlivinglife_intro_200306'];
        return Array.from(document.querySelectorAll('#detail > div.txt-manual img'))
            .map(img => img.src)
            .filter(src => !forbiddenSrces.some(forbiddenSrc => src.includes(forbiddenSrc)));
    });
}
async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('#mainImage > img').src;
    });
    return productImage;
}
async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('select.tune');
    }
    async function resetSelects() {
        const delBtn = await page.$('div.del > button');
        if (delBtn) {
            await delBtn.click();
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '')
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
                        if (optText.includes(' : ')) {
                            optText = optText.split(' : ')[0];
                        }
                        optionName = optionName.length > 0 ? `${optionName}, ${optText}` : optText;
                        optionName = optionName.replace(/\s*\+\d{1,3}(,\d{3})*원/g, "");
                    });
                    const optionPrice = await page.$eval('strong.total_price', el => el.textContent.trim().replace(/[^\d]/g, ''));
                    const productOption = { optionName, optionPrice };
                    productOptions.push(productOption);
                }
                await resetSelects();
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
    const productOptions = await processSelectOptions(selects);
    return productOptions;
}
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productName = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2').textContent.trim();
        return productName;
    });
    return productName;
}
async function getHasOption(page) {
    return await page.evaluate(() => {
        return document.querySelector('select.tune');
    });
}
