const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { getOptionName } = require('./extract_product_option');

/**
 * Compare product options with existing options.
 * @param {string} optionName - Option name extracted from the product detail.
 * @param {Array} existingOptions - Array of existing options.
 * @returns {boolean} - Returns true if the option is found (indicating sold out), otherwise false.
 */
function compareOptions(optionName, existingOptions) {
    return existingOptions.includes(optionName);
}

/**
 * Load existing options from result.json file.
 * @returns {Array} - Array of existing options.
 */
function loadExistingOptions() {
    try {
        const filePath = path.join(__dirname, 'result.json');
        const data = fs.readFileSync(filePath, 'utf8');
        if (data.trim().length === 0) {
            return [];
        }
        const soldOutProductIds = JSON.parse(data);
        return soldOutProductIds;
    } catch (error) {
        if (error.code === 'ENOENT') {
            return [];
        } else {
            console.error('Error reading result.json:', error);
            return [];
        }
    }
}

/**
 * Sign in to the website.
 * @param {object} page - Puppeteer page object.
 * @param {string} username - Username for login.
 * @param {string} password - Password for login.
 */
async function signIn(page, username, password) {
    try {
        await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle2', timeout: 120000 });
        await page.evaluate((username, password) => {
            document.querySelector('#member_id').value = username;
            document.querySelector('#member_passwd').value = password;
            document.querySelector('#contents > form > div > div > fieldset > a').click();
        }, username, password);
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 120000 });
        console.log('Successfully signed in');
    } catch (error) {
        console.error(`Failed to sign in: ${error.message}`);
        throw new Error('Sign in failed');
    }
}

/**
 * Check if the product page is valid.
 * @param {object} page - Puppeteer page object.
 * @param {string} productHref - Product URL.
 * @returns {string|boolean} - Returns 'error' for error, false for sold out, true for valid product.
 */
async function isValidProduct(page, productHref) {
    try {
        const soldOutImageSrc = await page.evaluate(() => {
            const soldOutSelector = '#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > span.icon > img';
            const soldOutImage = document.querySelector(soldOutSelector);
            return soldOutImage ? soldOutImage.src : null;
        });

        if (soldOutImageSrc && soldOutImageSrc.includes("img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif")) {
            console.log(`Product ${productHref} is sold out`);
            return false;
        }

        const errorImageExists = await page.evaluate(() => {
            const errorImage = document.querySelector('img[src="//img.echosting.cafe24.com/ec/image_admin/img_404.png"]');
            return !!errorImage;
        });

        if (errorImageExists) {
            console.log(`Product ${productHref} has an error image`);
            return 'error';
        }

        const productTitleExists = await page.evaluate(() => {
            const productTitle = document.querySelector('title');
            return !!productTitle;
        });

        if (!productTitleExists) {
            console.log(`Product ${productHref} has no title`);
            return 'error';
        }

        return true;
    } catch (error) {
        console.error(`Timeout or error checking product ${productHref}: ${error.message}`);
        return false;
    }
}

/**
 * Enter the product page.
 * @param {object} page - Puppeteer page object.
 * @param {string} productHref - Product URL.
 * @returns {boolean} - Returns true if successfully entered the product page.
 */
async function enterProductPage(page, productHref) {
    try {
        await new Promise(resolve => setTimeout(resolve, 2000));
        await page.goto(productHref, { waitUntil: 'networkidle2', timeout: 120000 });
        console.log(`Entered product page: ${productHref}`);
        return true;
    } catch (error) {
        console.error(`Failed to enter product page ${productHref}: ${error.message}`);
        return false;
    }
}

/**
 * Get product options from the page.
 * @param {object} page - Puppeteer page object.
 * @returns {Array} - Array of product options.
 */
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
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**')
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
                    console.log(`Processed option: ${optionName}, Price: ${optionPrice}`);
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

(async () => {
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox'],
        defaultViewport: null,
        timeout: 120000,
        protocolTimeout: 300000
    });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

        await signIn(page, username, password);
        const maxAttempts = 3;
        const soldOutProducts = [];
        const existingOptions = loadExistingOptions();

        for (const product of products) {
            const optionName = getOptionName(product.productDetail);

            let enterResult = false;
            let ivp = false;

            for (let attempt = 0; attempt < maxAttempts; attempt++) {
                try {
                    enterResult = await enterProductPage(page, product.productHref);
                    if (!enterResult) {
                        console.log(`Failed to enter product page: ${product.productHref}`);
                        break;
                    }

                    ivp = await isValidProduct(page, product.productHref);
                    if (ivp === true) {
                        if (product.hasOption) {
                            const productOptions = await getProductOptions(page);
                            for (const option of productOptions) {
                                const found = compareOptions(option.optionName, existingOptions);
                                if (found) {
                                    soldOutProducts.push(product.id);
                                    console.log(`Product ${product.id} with option ${option.optionName} is sold out`);
                                }
                            }
                        } else {
                            const found = compareOptions(optionName, existingOptions);
                            if (found) {
                                soldOutProducts.push(product.id);
                                console.log(`Product ${product.id} without options is sold out`);
                            }
                        }
                        break;
                    } else if (ivp === 'error') {
                        console.log(`Error in product validation: ${product.productHref}`);
                        break;
                    }
                } catch (error) {
                    console.error(`Error on attempt ${attempt + 1} for product ${product.productHref}: ${error.message}`);
                    if (attempt < maxAttempts - 1) {
                        console.log(`Retrying for product ${product.productHref}`);
                        await page.reload({ waitUntil: ["networkidle0", "domcontentloaded"], timeout: 120000 });
                    }
                }
            }

            // If product could not be processed or invalid, mark it as sold out
            if (!enterResult || !ivp || ivp === 'error') {
                soldOutProducts.push(product.id);
                console.log(`Product ${product.id} is marked as sold out due to error`);
            }
        }

        const sopFile = path.join(__dirname, 'result.json');
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProducts), 'utf8');
        console.log({ status: true, data: soldOutProducts });
    } catch (error) {
        console.error('Error:', error);
        console.log({ status: false, error: error.message });
    } finally {
        await browser.close();
    }
})();
