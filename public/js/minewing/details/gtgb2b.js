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

async function scrapePrices(page) {
    const hasOptions = await page.evaluate(() => {
        return document.querySelectorAll('select[name="opt[]"]').length > 0;
    });

    if (!hasOptions) {
        console.log("No options available.");
        return;
    }

    const selectElements = await page.$$('select[name="opt[]"]');
    for (const select of selectElements) {
        const optionValues = await select.evaluate(select => 
            Array.from(select.options)
                .filter((option, index) => option.value.trim() !== '' && index > 0)
                .map(option => option.value)
        );

        for (const optionValue of optionValues) {
            await select.select(optionValue);
            await page.waitForTimeout(1000);

            const finalPrice = await page.evaluate(() => {
                const priceElement = document.querySelector('.final-price');
                return priceElement ? priceElement.innerText.trim() : null;
            });

            // 필요한 경우 추가적인 작업 수행
        }
    }
}

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.goto('Your Product Page URL');
    await scrapePrices(page);
    await browser.close();
})();
