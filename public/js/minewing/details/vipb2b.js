const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        await page.setViewport({
            width: 1920,
            height: 1080
        });
        // const args = process.argv.slice(2);
        // const [tempFilePath, username, password] = args;
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const urls = ['https://www.vipb2b.co.kr/goods/goods_view.php?goodsNo=2661'];
        const username = 'sungil2018';
        const password = 'Tjddlf88!@';
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
    await page.goto('https://www.vipb2b.co.kr/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.evaluate(() => {
        document.querySelector('#formLogin').submit();
    });
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, productHref) {
    try {
        if (await page.$('input[name="optionTextInput_0"]')) {
            return false;
        }
        const productOptionData = await getProductOptions(page);
        const hasOption = productOptionData.hasOption;
        const productOptions = productOptionData.productOptions;
        if (!await page.$('#mainImage > img')) {
            return false;
        }
        const productImage = await getProductImage(page);
        const productDetail = await getProductDetail(page);
        if (productDetail === false) {
            return false;
        }
        const productName = await getProductName(page);
        const productPrice = 0;
        const product = {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref,
            sellerID: 45
        };
        return product;
    } catch (error) {
        return false;
    }
}
async function getProductDetail(page) {
    return await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#detail > div.detail_cont > div > div.txt-manual > center > img');
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
        const selectHandles = await page.$$('select.chosen-select'); // 모든 select.ProductOption0 요소를 선택
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
        const delBtn = await page.$('button.delete_goods');
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt =>
                    opt.value !== '' &&
                    opt.value !== '*' &&
                    opt.value !== '**' &&
                    !opt.text.includes("품절") &&
                    !opt.text.includes("준수"))
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
                            .replace(/\s\+\d+,\d+원/g, "")
                            .trim()
                    ).join(" / ");
                    const optionPrice = parseInt(await page.$eval('dl.total_amount > dd > strong', el => el.textContent.trim().replace(/[^\d]/g, '')));
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
}
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productNameElement = document.querySelector('#frmView > div > div > div.item_detail_tit > h3');
        let productNameText = productNameElement.textContent.trim();
        productNameText = productNameText.replace(/\(.*해외배송.*\)/g, '');
        return productNameText.trim();
    });

    return productName;
}

async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.chosen-select');
        const filteredSelectElements = Array.from(selectElements).filter(select => select.name !== 'deliveryCollectFl');
        if (filteredSelectElements.length > 0) {
            return true;
        }
        return false;
    });
}



