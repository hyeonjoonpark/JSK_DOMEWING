const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
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
            await page.goto(url, { waitUntil: 'networkidle0' });
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
    await page.goto('https://www.cheonyu.com/member/login.html?url=%2F', { waitUntil: 'networkidle0' });
    await page.type('#inMID', username);
    await page.type('#inMPW', password);
    await page.click('#frmLogin > input.newloginbtn.ptsans');
    await page.waitForNavigation();
}


async function scrapeProduct(page, productHref) {
    const product = await page.evaluate((productHref) => {
        const checkSoldout = document.querySelector('#productView > div.detaile_info_wrap > div.info_wrap > ul > li.prod_soldout');
        if (checkSoldout) {
            return false;
        }
        const productNameEl = document.querySelector('#productView > div.detaile_info_wrap > div.info_wrap > div.pdt_name.ptsans > span').textContent.trim();
        const productName = productNameEl.replace(/\[[^\]]*\]/g, "");
        const productPrice = document.querySelector('#productView > div.detaile_info_wrap > div.info_wrap > div.pdt_price_info > table > tbody > tr:nth-child(1) > td.price > strong > span').textContent.trim().replace(/[^\d]/g, '');
        const baseUrl = 'https://www.cheonyu.com';
        const productImageEl = document.querySelector('#PhotoMain').getAttribute('src').trim();
        if (!productImageEl) {
            return false;
        }
        const productImage = baseUrl + productImageEl;

        const images = document.querySelectorAll('#viewPcontent img');
        const productDetail = [];

        images.forEach((image) => {
            const imageUrl = image.getAttribute('src');
            if (imageUrl) {
                productDetail.push(imageUrl.trim());
            }
        });
        let hasOption = false;
        let productOptions = [];


        const skipOption = document.querySelector('#tdOption > label');
        if (!skipOption) {
            hasOption = true;
            const options = document.querySelectorAll('#opSelectedList > tbody tr');

            for (const option of options) {
                const optionName = option.querySelector('#tdOption').textContent.trim();

                const pattern = /\((\d+)개\)$/;
                const match = optionName.match(pattern);
                if (match) {
                    if (match[1] < 10) continue;
                }
                if (optionName.includes('품절')) {
                    continue;
                }

                const optionPriceEl = option.querySelector('#htmlPrice').textContent.trim();
                const optionPrice = optionPriceEl.replace(/[^\d-+]/g, '').trim()

                productOptions.push({ optionName, optionPrice });
            }
            if (productOptions.length === 0) {
                return false;
            }
        }

        return {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 50
        };
    }, productHref);

    return product;
}
