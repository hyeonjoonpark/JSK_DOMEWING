const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // const urls = ['https://www.gtgb2b.com/goods/goods_view.php?goodsNo=987'];
        // const username = 'sungil2018';
        // const password = 'tjddlf88!@';
        await login(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            if (product !== false) {
                products.push(product);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function login(page, username, password) {
    await page.goto('https://gtgb2b.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}
async function scrapeProduct(page, productHref) {
    try {
        const hasOption = await getHasOption(page);
        let productOptions = [];
        if (hasOption === true) {
            productOptions = await getProductOptions(page);
        }
        await page.evaluate(() => {
            // 대표 이미지
            const productImageThumbElements = document.querySelectorAll('#content > div.goods-view > div.goods > div > div.more-thumbnail > div.slide > div > div > div a');
            productImageThumbElements[productImageThumbElements.length - 1].click();
        });
        await new Promise(resolve => setTimeout(resolve, 1000));
        const product = await page.evaluate((productHref, productOptions, hasOption) => {
            const productNameElement = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2');
            const productName = productNameElement.textContent.trim();
            const productPriceText = document.querySelector('#frmView > div > div.item > ul > li.price > div > strong').textContent.trim();
            const productPrice = parseInt(productPriceText.replace(/[^\d]/g, ''));
            const productImage = document.querySelector('#mainImage > img').src;
            // 상세 이미지
            const productDetailElements = document.querySelectorAll('#detail > div.txt-manual > div:nth-child(12) > img');
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
                sellerID: 29
            };
        }, productHref, productOptions, hasOption);
        return product;
    } catch (error) {
        return false;
    }
}
// 페이지에서 상품의 가격 정보와 옵션 가격 정보를 추출하는 함수입니다.
async function scrapeProduct(page) {
    try {
        // 페이지에서 기본 가격 정보를 추출합니다.
        const price = await extractPrice(page);
        // 페이지에 옵션이 있는지 확인합니다.
        const hasOptions = await checkOptions(page);
        let optionPrices = [];

        // 옵션이 있다면, 각 옵션별 가격을 추출합니다.
        if (hasOptions) {
            optionPrices = await extractOptionPrices(page);
        }

        // 추출된 기본 가격과 옵션별 가격 정보를 반환합니다.
        return {
            price,
            optionPrices
        };
    } catch (error) {
        // 오류 발생 시 콘솔에 오류를 출력하고 null을 반환합니다.
        console.error('Error during scraping product:', error);
        return null;
    }
}

// 페이지에서 기본 가격 또는 할인 가격(있을 경우)을 추출하는 함수입니다.
async function extractPrice(page) {
    return page.evaluate(() => {
        // 할인 가격 요소와 최종 가격 요소를 찾습니다.
        const salePriceElement = document.querySelector('#frmView > div > div.item > ul > li.benefits > div > p.sale > strong');
        const finalPriceElement = document.querySelector('div.end-price > ul > li.total > strong');
        // 기본 가격 요소를 찾습니다.
        const basePriceElement = document.querySelector('#frmView > div > div.item > ul > li.price > div > strong');

        // 할인 가격이 있으면 최종 가격을, 없으면 기본 가격을 추출합니다.
        if (salePriceElement && finalPriceElement) {
            return parseInt(finalPriceElement.textContent.replace(/[^\d]/g, ''));
        } else if (basePriceElement) {
            return parseInt(basePriceElement.textContent.replace(/[^\d]/g, ''));
        }
        // 가격 정보가 없으면 null을 반환합니다.
        return null;
    });
}

// 페이지에 옵션이 있는지 확인하는 함수입니다.
async function checkOptions(page) {
    return page.evaluate(() => {
        // 옵션 요소들을 찾아서 옵션의 존재 여부를 확인합니다.
        const optionElements = document.querySelectorAll('#frmView > div > div:nth-child(5) > div > div > div > div > ul li, #frmView > div > div:nth-child(7) > div > div > div > div > ul li');
        return optionElements.length > 0;
    });
}

// 옵션별 가격 정보를 추출하고 선택된 옵션을 초기화하는 함수입니다.
async function extractOptionPrices(page) {
    const options = await page.evaluate(() => {
        const options = [];
        // 두 번째 옵션 선택자에서 옵션 요소들을 찾습니다.
        const optionElements = document.querySelectorAll('#frmView > div > div:nth-child(7) > div > div > div > div > ul li');
        // 첫 번째 옵션을 제외하고 각 옵션의 값을 배열에 추가합니다.
        optionElements.forEach((option, index) => {
            if (index > 0) { // 첫 번째 옵션은 건너뜁니다.
                options.push(option.value);
            }
        });
        return options;
    });

    let optionPrices = [];
    for (const optionValue of options) {
        // 선택된 옵션에 대한 가격을 추출합니다.
        await page.select('select[name="option2"]', optionValue);
        // 옵션 선택 후 가격 정보가 갱신될 때까지 기다립니다.
        await page.waitForSelector('div.end-price > ul > li.total > strong');

        const optionPrice = await page.evaluate(() => {
            // 최종 가격 요소를 찾아 값을 추출합니다.
            const priceElement = document.querySelector('div.end-price > ul > li.total > strong');
            return priceElement ? parseInt(priceElement.textContent.replace(/[^\d]/g, '')) : null;
        });
        optionPrices.push(optionPrice);

        // 선택된 옵션을 초기화합니다. 여기서는 페이지를 새로고침하는 방법을 사용했습니다.
        await page.reload({ waitUntil: ["networkidle0", "domcontentloaded"] });
        // 페이지가 완전히 로드될 때까지 기다립니다.
        await page.waitForSelector('#frmView');
    }

    return optionPrices; // 추출된 옵션별 가격 정보를 반환합니다.
}

