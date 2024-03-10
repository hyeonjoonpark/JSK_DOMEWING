const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        // const [tempFilePath, username, password] = args;
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const urls = ['https://www.unionpet.co.kr/goods/goods_view.php?goodsNo=13065'];
        const username = 'jskorea2023';
        const password = 'Tjddlf88!@';

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
        // await browser.close();
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

    // 옵션 2개짜리 수정해야함
    if (optionCount.length == 2) {
        await page.click('#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd > div > a');
        // const allSelectElements = await page.$$('a[class^="chosen-single]');
        const firstOptionValues = await page.evaluate(() => {
            const firstOptionElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd > div > div > ul li');
            const firstOptionValues = [];
            for (let i = 1; i < firstOptionElements.length; i++) {
                const optionValue = firstOptionElements[i].textContent.trim();
                firstOptionValues.push(optionValue);
            }
            return firstOptionValues;
        });




        for (let i = 1; i < firstOptionValues.length; i++) {
            const optionValue = firstOptionValues[i];
            await page.waitForSelector('select#frmView');//select를 못함
            await page.select('select#frmView > div > div > div.item_detail_list > div > dl:nth-child(3) > dd', optionValue);
            await page.click('#frmView > div > div > div.item_detail_list > div > dl:nth-child(5) > dd > div > a');


            const tmpProductOptions = await page.evaluate(() => {
                const firstOptionName = firstOptionValues[i].textContent.trim();
                const secondOptionElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl:nth-child(5) > dd > div > div > ul li');
                const options = [];
                const secondOptionElementsArray = Array.from(secondOptionElements).slice(1);

                for (let j = 0; j < secondOptionElementsArray.length; j++) {
                    const option = secondOptionElementsArray[j];
                    const sumOption = firstOptionName + option.textContent;
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
            return tmpProductOptions;
        };
        productOptions.push(...tmpProductOptions);

    }
    return productOptions;
}


async function scrapeProduct(page, productHref, options) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref, options) => {
        const productAmountElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > dl > dd');
        const productAmount = countProductAmount(productAmountElements); //상품 재고 세기
        if (productAmount < 10) {
            return false;
        }
        const productName = document.querySelector('#frmView > div > div > div.item_detail_tit > h3').textContent.trim();
        const productPrice = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd > strong > strong').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#mainImage > img').getAttribute('src').trim();
        const images = document.querySelectorAll('#detail > div.detail_cont > div > div.txt-manual > p img');
        let productDetail = [];
        if (images.length > 0) { //에러이미지는 하나도 읽지를 못함
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
