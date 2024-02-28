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
// 사용자 로그인을 처리하는 비동기 함수입니다.
async function login(page, username, password) {
    // 로그인 페이지로 이동합니다.
    await page.goto('http://www.autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    // 사용자 이름과 비밀번호 입력 필드에 값을 입력합니다.
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(2) > input[type=text]', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td:nth-child(2) > input[type=password]', password);
    // 로그인 버튼을 클릭합니다.
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input[type=image]');
    // 페이지 내비게이션이 완료될 때까지 기다립니다.
    await page.waitForNavigation({ waitUntil: 'networkidle0' });
}
async function scrapeProduct(page, productHref) {
    const hasOption = await getHasOption(page);
    let productOptions = [];
    if (hasOption === true) {
        productOptions = await getProductOptions(page);
    }
    const product = await page.evaluate((productHref, productOptions, hasOption) => {
        // productName
        const productNameElement = document.querySelector('#goods_spec > form > div:nth-child(4) > b');
        productNameElement.querySelectorAll('font[color="red"]').forEach(el => el.remove());
        const productName = productNameElement.textContent.trim();
        // productPrice
        const productPriceText = document.querySelector('#price').textContent.trim();
        const productPrice = parseInt(productPriceText.replace(/[^\d]/g, ''));
        // productImage
        const imageElement = document.querySelector('div:nth-child(1) > a > img');
        const productImage = imageElement.src; // 이미지의 절대 URL을 반환
        // productDetail
        const productDetailElements = document.querySelectorAll('#contents img');
        const productDetail = [];
        for (const productDetailElement of productDetailElements) {
            const productDetailImage = productDetailElement.src
            productDetail.push(productDetailImage);
        }
        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 20
        };
    }, productHref, productOptions, hasOption);
    return product;
}
async function getProductOptions(page) {
    // Retrieve all select elements.
    const selectElements = await page.$$('select[name="opt[]"]');

    // Initialize an array to hold all product options.
    let productOptions = [];

    // Ensure that there are at least two select elements.
    if (selectElements.length > 1) {
        // Retrieve values and texts of options from the first select element.
        const firstOptionValues = await selectElements[0].evaluate(select =>
            Array.from(select.options)
                .filter(option => option.value.trim() !== '')
                .map(option => ({ value: option.value, text: option.textContent.trim() }))
        );

        // Loop through each option value of the first select element.
        for (const { value: firstOptionValue, text: firstOptionText } of firstOptionValues) {
            // Select the option in the first select element.
            await selectElements[0].select(firstOptionValue);

            // Wait for any dynamic content to load.
            await new Promise((page) => setTimeout(page, 1000));

            // Evaluate the second select element after selecting an option in the first.
            const secondSelectOptions = await selectElements[1].evaluate(select =>
                Array.from(select.options)
                    .filter(option => option.value.trim() !== '') // 빈 값이 아닌 모든 옵션을 포함
                    .map(option => {
                        const text = option.textContent.trim();
                        let name = text, price = 0; // 기본적으로 가격을 0으로 설정
                        // 가격 정보가 있는 경우, 이름과 가격을 분리
                        if (text.includes('(')) {
                            const parts = text.split('(');
                            name = parts[0].trim();
                            const pricePart = parts[1];
                            if (pricePart && pricePart.includes('원')) {
                                price = parseInt(pricePart.replace(/[^\d-+]/g, ''), 10);
                            }
                        }
                        return { name, price };
                    })
            );

            // Combine options from the first and second select elements and add them to the productOptions array.
            secondSelectOptions.forEach(({ name: secondOptionName, price }) => {
                productOptions.push({
                    optionName: `${firstOptionText} ${secondOptionName}`,
                    optionPrice: price,
                });
            });
        }
    } else {
        productOptions = await selectElements[0].evaluate(select =>
            Array.from(select.options)
                .filter(option => option.value.trim() !== '') // 빈 값이 아닌 모든 옵션을 포함
                .map(option => {
                    const text = option.textContent.trim();
                    let optionName = text, optionPrice = 0; // 기본적으로 가격을 0으로 설정
                    // 가격 정보가 있는 경우, 이름과 가격을 분리
                    if (text.includes('(')) {
                        const parts = text.split('(');
                        optionName = parts[0].trim();
                        const pricePart = parts[1];
                        if (pricePart && pricePart.includes('원')) {
                            optionPrice = parseInt(pricePart.replace(/[^\d-+]/g, ''), 10);
                        }
                    }
                    return { optionName, optionPrice };
                })
        );
    }

    return productOptions;
}
async function getHasOption(page) {
    // Directly return the evaluation result.
    return page.evaluate(() => document.querySelectorAll('select[name="opt[]"]').length > 0);
}