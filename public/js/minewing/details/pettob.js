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
    await page.goto('https://pettob.co.kr/shop/main/intro_member.php?returnUrl=%2Fshop%2Fmain%2Findex.php', { waitUntil: 'networkidle0' });
    await page.type('#login_frm > div:nth-child(3) > input', username);
    await page.type('#login_frm > div:nth-child(4) > input', password);
    await page.click('#login_frm > div:nth-child(5) > a.btn.btn-default.btn-lg.submit.btn_login');
    await page.waitForNavigation();
}

async function scrapeProduct(page, productHref) {
    await new Promise((page) => setTimeout(page, 1000));
    const product = await page.evaluate((productHref) => {
        const skipProduct = document.querySelector('#goods_spec > form > table:nth-child(6) > tbody > tr:nth-child(6) > td > strong');
        if (skipProduct) {
            if (checkSkipProduct(skipProduct.textContent)) {
                return false;
            }
        }
        const productNameEl = document.querySelector('#goods_spec > form > div.info_name.bootstrap').textContent.trim();
        const productName = removeExpiration(productNameEl);

        const productPriceEl = document.querySelector('#price').textContent.trim();
        const productPrice = checkSalePrice(productPriceEl).replace(/[^\d]/g, '');
        const productImage = document.querySelector('#objImg').src.trim();



        const images = document.querySelectorAll('#contents > table > tbody > tr > td > p img');
        const productDetailImageElement = [];
        images.forEach((image) => {
            const imageUrl = image.src.trim();
            productDetailImageElement.push(imageUrl);
        });
        const productDetail = productDetailImageElement;





        const optionElement = document.querySelector('#el-sOption');
        let hasOption = false;
        let productOptions = [];
        if (optionElement) {
            hasOption = true;
            const optionElements = document.querySelectorAll('#el-sOption option');
            for (let i = 1; i < optionElements.length; i++) {
                const optionElement = optionElements[i];
                const optionText = optionElement.textContent.trim();
                let optionName, optionPrice;
                if (optionText.includes('0원')) {
                    const optionFull = optionText.split(':');
                    optionName = optionFull[0].trim();
                    optionPrice = optionFull[1].replace(/[^\d-+]/g, '').trim();
                    optionPrice = parseInt(optionPrice, 10);
                } else {
                    optionName = optionText.trim();
                    optionPrice = 0;
                }
                if (checkSoldOutOptions(optionName)) continue;
                productOptions.push({ optionName, optionPrice });
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
            sellerID: 30
        };
        function checkSoldOutOptions(optionName) {
            if (optionName.includes(' [품절]')) {
                return true;
            }
            return false;
        }
        function checkSkipProduct(skipProduct) {
            if (skipProduct.includes('반품불가')) {
                return true;
            }
            return false;
        }
        function removeExpiration(nameElement) {
            if (nameElement.includes(' - 유통기한')) {
                const name = nameElement.split(' - 유통기한')[0];
                return name;
            }
            return nameElement;
        }
        function checkSalePrice(salePrice) {
            let sellPrice = salePrice;
            if (salePrice.includes('원')) {
                sellPrice = salePrice.split('원')[0];
            }
            return sellPrice;
        }
    }, productHref);
    return product;

}
