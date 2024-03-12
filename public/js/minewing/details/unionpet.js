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
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const productOptions = await checkedOption(page);
            const product = await scrapeProduct(page, url, productOptions);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) {
        try {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            return true;
        } catch (error) {
            if (i < attempts - 1) {
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    return false;
}
async function signIn(page, username, password) {
    await page.goto('https://www.unionpet.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.evaluate((username, password) => {
        document.querySelector('#loginId').value = username;
        document.querySelector('#loginPwd').value = password;
        document.querySelector('#formLogin > div.member_login_box > div.login_input_sec > button').click();
    }, username, password);
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}
async function checkedOption(page) {
    const hasOption = await page.evaluate(() => {
        const optionElement = document.querySelector('#frmView > div > div > div > div > dl > dd > div');
        let hasOption = false;
        if (optionElement) {
            hasOption = true;
        }
        return hasOption;
    });
    let options = [];
    if (hasOption === true) {
        options = await scrapeProductOptions(page);
    }
    const optionSet = { hasOption, options };
    return optionSet;
}


async function scrapeProductOptions(page) {
    const optionCount = await page.$$('#frmView > div > div > div > div > dl > dd > div');
    const productOptions = [];
    if (optionCount.length == 1) {
        await page.click('#frmView > div > div > div.item_detail_list > div > dl > dd > div');
        const productOptionsEl = await page.evaluate(() => {
            const firstOption = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl > dd > div > div > ul li');
            const options = [];
            for (let i = 1; i < firstOption.length; i++) {
                const optionElement = firstOption[i];
                const optionText = optionElement.textContent.trim().replace(/\n/g, '');
                let optionName, optionPrice;
                if (optionText.includes('품절')) {
                    continue;
                }
                const optionElements = optionText.split(' : ');
                if (optionElements.length > 1 && optionElements[1].includes('개')) {
                    const productOptionAmount = parseInt(optionElements[1].replace(/\D/g, ''), 10);
                    if (productOptionAmount < 10) {
                        continue;
                    }
                    optionName = optionElements[0];
                    optionPrice = 0;
                } else if (optionElements.length > 2 && optionElements[1].includes('원')) {
                    const productOptionAmount = parseInt(optionElements[2].replace(/\D/g, ''), 10);
                    if (productOptionAmount < 10) {
                        continue;
                    }
                    optionName = optionElements[0];
                    optionPrice = parseInt(optionElements[1].replace(/[^\d-+]/g, ''), 10);

                } else {
                    continue;
                }
                options.push({ optionName, optionPrice });
            }
            return options;
        });
        productOptions.push(...productOptionsEl);
    }

    if (optionCount.length == 2) {
        await new Promise(resolve => setTimeout(resolve, 2000));
        await page.click('#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd > div > a > span');
        const allSelectElements = await page.$$('a[class^="chosen-single"]');
        if (allSelectElements.length > 0) {
            await page.click('#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd > div > a > span');
            const firstOptionValues = await page.evaluate(() => {
                const firstOptionElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd > select option');
                const firstOptionValues = [];
                for (let i = 1; i < firstOptionElements.length; i++) {
                    const optionValue = firstOptionElements[i].value;
                    firstOptionValues.push(optionValue);
                }
                console.log(firstOptionValues);
                return firstOptionValues;
            });

            for (let i = 0; i < firstOptionValues.length; i++) {
                const optionValue = firstOptionValues[i];
                await page.select('#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd > select', optionValue);
                await new Promise(resolve => setTimeout(resolve, 1000));
                await page.click('#frmView > div > div > div.item_detail_list > div > dl:nth-child(5) > dd > div > a > span');

                const tmpProductOptions = await page.evaluate((firstOptionValues, i) => {
                    const firstOptionName = firstOptionValues[i];
                    const secondOptionElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl:nth-child(5) > dd > select option');
                    const options = [];
                    const secondOptionElementsArray = Array.from(secondOptionElements).slice(1);

                    for (let j = 0; j < secondOptionElementsArray.length; j++) {
                        const option = secondOptionElementsArray[j].textContent;
                        const sumOption = firstOptionName + option;
                        const optionText = sumOption.trim().replace(/\n/g, '');
                        let optionName, optionPrice;

                        if (optionText.includes('품절')) {
                            continue;
                        }

                        const optionElements = optionText.split(' : ');
                        if (optionElements.length > 1 && optionElements[1].includes('개')) {
                            const productOptionAmount = parseInt(optionElements[1].replace(/\D/g, ''), 10);
                            if (productOptionAmount < 10) {
                                continue;
                            }
                            optionName = optionElements[0];
                            optionPrice = 0;
                        } else if (optionElements.length > 2 && optionElements[2].includes('개')) {
                            const productOptionAmount = parseInt(optionElements[2].replace(/\D/g, ''), 10);
                            if (productOptionAmount < 10) {
                                continue;
                            }
                            optionName = optionElements[0];
                            optionPrice = parseInt(optionElements[1].replace(/[^\d-+]/g, ''), 10);
                        } else {
                            continue;
                        }
                        options.push({ optionName, optionPrice });
                    }
                    return options;

                }, firstOptionValues, i);

                productOptions.push(...tmpProductOptions);
            }
        }
    }
    return productOptions;
}


async function scrapeProduct(page, productHref, options) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref, options) => {
        const soldOutProduct = document.querySelector('#frmView > div > div > div.btn_choice_box.btn_restock_box > button');
        if (soldOutProduct) {
            return false;
        }
        const productAmountElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > dl > dd');
        const productAmount = countProductAmount(productAmountElements);
        if (productAmount < 10) {
            return false;
        }
        const productName = document.querySelector('#frmView > div > div > div.item_detail_tit > h3').textContent.trim();
        const productPrice = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd > strong > strong').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#mainImage > img').getAttribute('src').trim();
        const images = document.querySelectorAll('#detail > div.detail_cont > div > div.txt-manual img');
        let productDetail = [];
        if (images.length > 0) {
            const productDetailImageElement = [];
            if (images.length > 0) {
                images.forEach((image) => {
                    const imageUrl = image.getAttribute('src').trim();
                    productDetailImageElement.push(imageUrl);
                });
            }
            productDetail = productDetailImageElement;
        }

        const hasOption = options.hasOption;
        const productOptions = options.options;
        if (hasOption == true && productOptions.length == 0) {
            return false;
        }

        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 24
        };
        function countProductAmount(productAmountElements) {
            const productAmountElement = productAmountElements[productAmountElements.length - 1];
            const productAmount = parseInt(productAmountElement.textContent.trim().replace(/\D/g, ''), 10);
            return productAmount;
        }
    }, productHref, options);
    return product;
}
