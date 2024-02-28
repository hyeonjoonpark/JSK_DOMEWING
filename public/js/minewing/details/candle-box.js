const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        const urls = ['https://candle-box.com/product/%EC%9B%90%ED%98%95%EC%96%91%EB%A9%B4-%ED%85%8C%EC%9D%B4%ED%94%84/1983/category/49/display/1/'];
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            if (navigateWithRetryResult === false) {
                continue;
            }
            const productOptions = await checkedOption(page, url);
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
    await page.goto('https://candle-box.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('a[class="btnLogin"]');
    await page.waitForNavigation();
}

async function checkedOption(page, productHref) {
    const productOption = await page.evaluate(async (productHref) => {
        const optionElement = document.querySelector('#product_option_id1');
        let hasOption = false;
        let productOptions = [];

        if (optionElement) {
            hasOption = true;
        }

        if (hasOption) {
            const options = await scrapeProductOptions(page, productHref);
            productOptions.push(options);
        }

        return { hasOption, productOptions };
    }, productHref);

    return productOption;
}


function scrapeProductOptions(page, productHref) {
    const allSelectElements = page.$$('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > table > tbody:nth-child(2)');
    let productOptions = [];
    if (allSelectElements.length > 0) {// 옵션이 있다.
        if (allSelectElements.length == 1) {//옵션이 1개
            const optionElements = document.querySelectorAll('#product_option_id1 > optgroup option');

            for (let i = 0; i < optionElements.length; i++) {
                const optionElement = optionElements[i];
                const optionText = optionElement.textContent.trim();
                let optionName, optionPrice;

                if (optionText.includes('원')) {
                    const optionFull = optionText.split(' (');
                    optionName = optionFull[0].trim();
                    optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                    optionPrice = parseInt(optionPrice, 10);
                } else {
                    optionName = optionText.trim();
                    optionPrice = 0;
                }
                productOptions.push({ optionName, optionPrice });
                //console.log({ optionName, optionPrice }); //옵션이 1개일때 잘 가져옴

            }
        }

        if (allSelectElements.length == 2) {//옵션이 2개
            const selectEle = allSelectElements[0].$('tr > td > select');//첫번째 옵션을 고른다.
            const options = selectEle.$$('option');//첫번째 옵션들을 가져온다.
            for (let i = 2; i < options.length; i++) {//첫번째 옵션의 반복문을 돌린다.
                const option = options[i];//첫번째 옵션의 i번째 옵션을 option으로 정의하고
                selectEle.select(option);// 첫번째 옵션의 i번째 옵션 중 첫번째를 선택한다.
                const optionElement = document.querySelector('#product_option_id1 > option')[i].textContent.trim();//첫번째 옵션의 이름을 정의하고
                const secOptions = document.querySelectorAll('#product_option_id2 option');//두번째 옵션들을 가져온다.
                for (let j = 2; j < secOptions.length; j++) {//선택했을때 가져오는 2번째 옵션의 length만큼 반복문을 할거다
                    const optionElement2 = document.querySelectorAll('#product_option_id2 > option')[j].textContent;
                    const optionText = optionElement + ' ' + optionElement2; //첫번째 옵션과 두번째 옵션의 이름을 합친다.

                    let optionName, optionPrice;

                    if (optionText.includes('원')) {
                        const optionFull = optionText.split(' (');
                        optionName = optionFull[0].trim();
                        optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                        optionPrice = parseInt(optionPrice, 10);
                    } else {
                        optionName = optionText.trim();
                        optionPrice = 0;
                    }
                    productOptions.push({ optionName, optionPrice });
                    // console.log({ optionName, optionPrice });
                }
            }

        }
    }
    return productOptions;
}
async function scrapeProduct(page, productHref, options) {//여기에 옵션을 넣어주고 있으면 넣어주고 없으면 안넣으면 되겠네?
    const product = await page.evaluate((productHref, options) => {
        const rawName = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > h2').textContent;
        const productName = removeSoldOutMessage(rawName);
        const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
        const productImage = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.xans-element-.xans-product.xans-product-image.imgArea > div.keyImg > div > a > img').getAttribute('src');
        const images = document.querySelectorAll('#contents > div.xans-element-.xans-product.xans-product-additional img');
        const additionalProducts = document.querySelector("#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.xans-element-.xans-product.xans-product-addproduct.productSet.additional > div > h3");
        if (additionalProducts) {
            return false;
        }
        if (images.length < 1) {
            return false;
        }
        const productDetail = Array.from(images, img => {
            let src = img.getAttribute('src');
            return src;
        });
        const hasOption = options.hasOption;
        const productOptions = options.productOptions;
        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 19
        };
        function removeSoldOutMessage(rawName) {
            const productName = rawName.trim();
            if (productName.includes('-품절시 단종')) {
                return productName.replace('-품절시 단종', '');
            }
            return productName;
        }
    }, productHref, options);
    return product;
}
