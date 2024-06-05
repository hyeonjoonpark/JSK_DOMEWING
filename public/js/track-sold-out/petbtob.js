const puppeteer = require('puppeteer');
const fs = require('fs');
const { getOptionName } = require('./extract_product_option');

/**
 * Compare product options with existing options.
 * @param {string} optionName - Option name extracted from the product detail.
 * @param {Array} existingOptions - Array of existing options.
 * @returns {boolean} - Returns true if the option is not found (indicating sold out), otherwise false.
 */
function compareOptions(optionName, existingOptions) {
    return !existingOptions.includes(optionName);
}

/**
 * Load existing options from result.json file.
 * @returns {Array} - Array of existing options.
 */
function loadExistingOptions() {
    const data = fs.readFileSync('result.json', 'utf8');
    const soldOutProducts = JSON.parse(data);
    return soldOutProducts.map(product => product.optionName);
}

(async () => {
    const browser = await puppeteer.launch({
        headless: false,
        args: ['--no-sandbox'],
        defaultViewport: null,
        timeout: 60000 // 60초로 시간 초과 설정을 증가시킴
    });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const maxAttempts = 3;
        const soldOutProducts = [];
        const existingOptions = loadExistingOptions(); // 기존 옵션명 배열화

        for (const product of products) {
            const optionName = getOptionName(product.productDetail); // 기존 상품 옵션 추적 메서드
            console.log(`Extracted option name for product ${product.id}: ${optionName}`);

            const enterResult = await enterProductPage(page, product.productHref, maxAttempts, 0);
            if (enterResult === false) {
                const soldOutProduct = {
                    id: product.id,
                    status: false
                };
                soldOutProducts.push(soldOutProduct);
                console.log(`Product ${product.id} is not available (enter result).`);
                continue;
            }

            const ivp = await isValidProduct(page, product.productHref, maxAttempts, 0);
            if (ivp === false) {
                const soldOutProduct = {
                    id: product.id,
                    status: false
                };
                soldOutProducts.push(soldOutProduct);
                console.log(`Product ${product.id} is not available (invalid product).`);
            } else {
                const productOptions = await getProductOptions(page);
                for (const option of productOptions) {
                    const found = compareOptions(option.optionName, existingOptions);
                    const soldOutProduct = {
                        id: product.id,
                        optionName: option.optionName,
                        status: found
                    };
                    soldOutProducts.push(soldOutProduct);
                    console.log(`Product ${product.id} option ${option.optionName} found: ${!found}`);
                }
            }
        }

        // 결과를 result.json 파일에 저장
        fs.writeFileSync('result.json', JSON.stringify(soldOutProducts, null, 2));
        console.log('Results saved to result.json');
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

async function enterProductPage(page, productHref, maxAttempts, attempt) {
    try {
        await page.goto(productHref, { waitUntil: 'domcontentloaded' });
        return true;
    } catch (error) {
        if (attempt >= maxAttempts) {
            return false;
        }
        return await enterProductPage(page, productHref, maxAttempts, attempt + 1);
    }
}

async function signIn(page, username, password) {
    await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.evaluate((username, password) => {
        document.querySelector('#member_id').value = username;
        document.querySelector('#member_passwd').value = password;
        document.querySelector('#contents > form > div > div > fieldset > a').click();
    }, username, password);
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function isValidProduct(page, productHref, maxAttempts, attempt) {
    try {
        return await page.evaluate(() => {
            const soldOutSelector = '#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > span.icon > img';
            const soldOutImage = document.querySelector(soldOutSelector);
            if (soldOutImage && soldOutImage.src.includes("img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif")) {
                return false;
            }
            const errorImage = document.querySelector('img[src="//img.echosting.cafe24.com/ec/image_admin/img_404.png"]');
            if (errorImage) {
                return 'error';
            }
            return true;
        });
    } catch (error) {
        return 'error';
    }
}

async function getProductOptions(page) {
    async function reloadSelects() {
        return page.$$('select.ProductOption0');
    }

    async function resetSelects() {
        const delBtn = await page.$('#option_box1_del');
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
                opts.map(opt => ({ value: opt.value, text: opt.text }))
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**' && !opt.text.includes("품절"))
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];

                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([\+\-]?\d{1,3}(,\d{3})*원\)/g, "").trim()
                    ).join(", ");
                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/\(([\+\-]?\d{1,3}(,\d{3})*원)\)/);
                        return total + (matches ? parseInt(matches[1].replace(/,|원|\+/g, ''), 10) : 0);
                    }, 0);
                    productOptions.push({ optionName, optionPrice });
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

    const selects = await reloadSelects();
    return processSelectOptions(selects);
}
