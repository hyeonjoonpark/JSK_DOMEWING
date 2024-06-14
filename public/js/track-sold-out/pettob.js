const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { getOptionName } = require('./extract_product_option');

//옵션 비교
function compareOptions(optionName, existingOptions) {
    return existingOptions.includes(optionName);
}

//옵션 배열
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
        await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle2' });
        await page.evaluate((username, password) => {
            document.querySelector('#member_id').value = username;
            document.querySelector('#member_passwd').value = password;
            document.querySelector('#contents > form > div > div > fieldset > a').click();
        }, username, password);
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
    } catch (error) {
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
            return false;
        }

        const errorImageExists = await page.evaluate(() => {
            const errorImage = document.querySelector('img[src="//img.echosting.cafe24.com/ec/image_admin/img_404.png"]');
            return !!errorImage;
        });

        if (errorImageExists) {
            return 'error';
        }

        const productTitleExists = await page.evaluate(() => {
            const productTitle = document.querySelector('title');
            return !!productTitle;
        });

        if (!productTitleExists) {
            return 'error';
        }

        return true;
    } catch (error) {
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
        await page.goto(productHref, { waitUntil: 'networkidle2' });
        return true;
    } catch (error) {
        return false;
    }
}

/**
 * Get product options from the page.
 * @param {object} page - Puppeteer page object.
 * @returns {Array} - Array of product options.
 */
async function scrapeProductOptions(page, productHref) {
    await page.goto(productHref);
    await new Promise(resolve => setTimeout(resolve, 1000));

    const productOptions = await page.evaluate(() => {
        const optionElement = document.querySelector('#el-sOption');
        if (!optionElement) {
            return [];
        }

        const optionElements = document.querySelectorAll('#el-sOption option');
        const options = [];

        for (let i = 1; i < optionElements.length; i++) {
            const optionElement = optionElements[i];
            const optionText = optionElement.textContent.trim();
            let optionName, optionPrice;

            if (optionText.includes('0원')) {
                const optionFull = optionText.split(':');
                optionName = optionFull[0].trim();
                optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                optionPrice = parseInt(optionPrice, 10);
            } else {
                optionName = optionText.trim();
                optionPrice = 0;
            }

            options.push({ optionName, optionPrice });
        }

        return options;
    });

    return productOptions;
}



(async () => {
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox'],
        defaultViewport: null,
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
                                }
                            }
                        } else {
                            const found = compareOptions(optionName, existingOptions);
                            if (found) {
                                soldOutProducts.push(product.id);
                            }
                        }
                        break;
                    } else if (ivp === 'error') {
                        break;
                    }
                } catch (error) {
                    if (attempt < maxAttempts - 1) {
                        await page.reload({ waitUntil: ["domcontentloaded"] });
                    }
                }
            }

            // If product could not be processed or invalid, mark it as sold out
            if (!enterResult || !ivp || ivp === 'error') {
                soldOutProducts.push(product.id);
            }
        }

        const sopFile = path.join(__dirname, 'result.json');
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProducts), 'utf8');
    } catch (error) {
        return false;
    } finally {
        await browser.close();
    }
})();
