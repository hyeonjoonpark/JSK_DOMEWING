const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
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
    await page.goto('https://dome4u.co.kr/home/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#id', username);
    await page.type('#passwd', password);
    await page.click('#login_submit');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    try {
        const productPrice = await page.evaluate(() => {
            const priceSelector = '#buy_info > div > div.detailView.type2 > dl > dd.priceView > ul > li:nth-child(1) > strong';
            const productPriceText = document.querySelector(priceSelector)?.textContent.trim();
            const productPrice = parseInt(productPriceText.replace(/[^\d]/g, ''), 10);
            return productPrice;
        });

        if (!productPrice) {
            console.error('Product price could not be scraped.');
            return null;
        }

        const { hasOption, productOptions } = await getHasOption(page, productPrice);

        return await page.evaluate((productHref, hasOption, productOptions, productPrice) => {
            try {
                const productNameSelector = '#buy_info > div > div.detailView.type2 > dl > dt > span';
                const productNameElements = document.querySelectorAll(productNameSelector);
                const productNameElement = Array.from(productNameElements).find(el => !el.classList.contains('ownershop'));
                const productName = productNameElement ? productNameElement.textContent.trim() : '';

                const imageSelector = '#viewImage_m';
                const imageElement = document.querySelector(imageSelector);
                const productImage = imageElement ? imageElement.src : '';

                const detailSelector = '#content .detailInfo.detailInner img';
                const productDetailElement = document.querySelector(detailSelector);
                const productDetail = productDetailElement ? productDetailElement.src : null;

                return {
                    productName,
                    productPrice,
                    productImage,
                    productDetail,
                    hasOption,
                    productOptions,
                    productHref,
                    sellerID: 32
                };
            } catch (error) {
                console.error('Error in product details extraction:', error);
                return false;
            }
        }, productHref, hasOption, productOptions, productPrice);
    } catch (error) {
        console.error('Error occurred while scraping product:', error);
        return null;
    }
}

async function getHasOption(page, productPrice) {
    productPrice = parseInt(productPrice, 10);
    const optionSelector = '#buy_info > div > div.detailView.type2 > dl > dd.optionView > select';

    const productOptions = [];

    try {
        const options = await page.$$(`${optionSelector} > option`);

        // 첫 번째 옵션('선택하세요'와 같은 문구가 포함됨)을 제외하고 처리
        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            const optionText = await (await option.getProperty('textContent')).jsonValue();

            if (optionText.includes('품절')) continue;

            let optionName, optionPriceDiff;
            const regex = /(.+)\s\(([\d,]+)원\)/;
            const matches = optionText.match(regex);

            if (matches) {
                optionName = matches[1].trim();
                let optionPrice = parseInt(matches[2].replace(/[^\d]/g, ''), 10);
                optionPriceDiff = optionPrice - productPrice;
            } else {
                optionName = optionText.trim();
                optionPriceDiff = 0; // 추가 비용 없음
            }

            productOptions.push({ optionName, optionPriceDiff });
        }

        return { hasOption: productOptions.length > 0, productOptions };
    } catch (error) {
        console.error('Error occurred while getting options:', error);
        return { hasOption: false, productOptions: [] };
    }
}
