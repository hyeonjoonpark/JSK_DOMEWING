const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://living9.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('#member_login_module_id > fieldset > div.login__button > a.btnSubmit.gFull.sizeL');
    await page.waitForNavigation();
}
async function scrapeProduct(page, productHref) {
    try {
        const productImage = await getProductImage(page);
        if (productImage.includes('img_product_big.gif')) {
            return false;
        }
        const productDetail = await getProductDetail(page);
        if (productDetail === false) {
            return false;
        }
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption ? await getProductOptions(page) : [];
        const productPrice = await page.evaluate(() => {
            const priceElement = document.querySelector('#span_product_price_text');
            let textContent = '';
            priceElement.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    textContent += node.textContent;
                }
            });
            const productPrice = textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 37
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}

async function getProductDetail(page) {
    try {
        await page.evaluate(async () => {
            const distance = 100;
            const scrollInterval = 5;
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
        await page.waitForSelector('#prdDetail img', { timeout: 10000 });
        const imageUrls = await page.evaluate(() => {
            const productDetailElements = document.querySelectorAll('#prdDetail img');
            return Array.from(productDetailElements)
                .map(element => element.src)
                .filter(src => !src.includes('cafe24'));
        });

        return imageUrls.length > 0 ? imageUrls : null;
    } catch (error) {
        console.error(`An error occurred while getting product details: ${error.message}`);
        return null;
    }
}
async function getProductDetail(page) {
    try {
        await page.evaluate(async () => {
            const distance = 100;
            const scrollInterval = 5;
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
        const imageUrls = await page.evaluate(() => {
            const productDetailElements = document.querySelectorAll('#prdDetail img');
            return Array.from(productDetailElements)
                .map(element => element.src)
                .filter(src => !src.includes('cafe24') && !src.includes('story') && !src.includes('notice'));
        });

        return imageUrls.length > 0 ? imageUrls : null;
    } catch (error) {
        console.error(`An error occurred while getting product details: ${error.message}`);
        return null;
    }
}

async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('div.xans-element-.xans-product.xans-product-image.imgArea > div.RW > div.prdImg > div > a > img').src;
    });
    return productImage;
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

async function getProductName(page) {
    try {
        await page.waitForSelector('#contents > div.xans-element-.xans-product.xans-product-detail.section > div.detailArea > div.infoArea > div > h1', { timeout: 5000 });

        const productName = await page.evaluate(() => {
            const productNameElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail.section > div.detailArea > div.infoArea > div > h1');
            if (productNameElement !== null) {
                let productNameText = productNameElement.textContent.trim();
                productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '').trim();
                return productNameText;
            } else {
                console.error('Product name element not found');
                return null;
            }
        });

        if (productName === null || productName === '') {
            throw new Error('Product name not found or is empty');
        }

        return productName;
    } catch (error) {
        console.error(`Error occurred while getting product name: ${error.message}`);
        return null;
    }
}

async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.ProductOption0');
        if (selectElements.length > 0) {
            return true;
        }
        return false;
    });
}
