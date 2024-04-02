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
            try {
                await page.goto(url, { waitUntil: 'domcontentloaded' });
            } catch (error) {
                continue;
            }
            const product = await scrapeProduct(page, url);
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
async function signIn(page, username, password) {
    await page.goto('http://bonniepet.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation();
}
async function scrapeProduct(page, productHref) {
    try {
        const productImage = await getProductImage(page);
        if (productImage.includes('img_detail_jpg.jpg')) {
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
            const productPrice = document.querySelector('#frmView div.item > ul > li.price > div > strong').textContent.trim().replace(/[^\d]/g, '');
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
            sellerID: 38
        };
        return product;
    } catch (error) {
        return false;
    }
}
async function getProductDetail(page) {

    return await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#detail > div.txt-manual img');
        if (productDetailElements.length > 0) {
            return Array.from(productDetailElements, element => element.src);
        }
        return false;
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
        const selectHandles = await page.$$('select.tune'); // 모든 select.ProductOption0 요소를 선택
        const filteredHandles = [];

        for (const handle of selectHandles) {
            const name = await page.evaluate(el => el.name, handle); // 각 요소의 name 속성 가져오기
            if (name !== 'deliveryCollectFl') { // 조건에 맞지 않는 이름 필터링
                filteredHandles.push(handle);
            }
        }

        return filteredHandles; // 필터링된 ElementHandle 배열 반환
    }

    async function resetSelects() {
        const delBtn = await page.$('div[id^="option_display_item_"] > div > div.del > button');
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
                    .filter(opt =>
                        opt.value !== '' &&
                        opt.value !== '*' &&
                        opt.value !== '**' &&
                        !opt.text.includes("품절") &&
                        !opt.text.includes("준수") // "준수" 포함 옵션 건너뛰기
                    )
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
                        opt.text
                            .replace(/\s*:.*/g, "")
                            .trim()
                    ).join(", ");

                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/[\+\-]?\d{1,3}(,\d{3})*원/);
                        return total + (matches ? parseInt(matches[0].replace(/,|원|\+/g, ''), 10) : 0);
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
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2');
        let productNameText = productNameElement.textContent.trim();
        productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '');
        return productNameText.trim();
    });

    return productName;
}

async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.tune');
        const filteredSelectElements = Array.from(selectElements).filter(select => select.name !== 'deliveryCollectFl');
        if (filteredSelectElements.length > 0) {
            return true;
        }
        return false;
    });
}



