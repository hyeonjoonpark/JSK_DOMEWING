const fs = require('fs');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://aadome.com/member/login.html', { waitUntil: 'networkidle0' });
    await page.type('#member_id', username);
    await page.type('#member_passwd', password);
    await page.click('#member_login_module_id > fieldset > div.login__button > a.btnSubmit.gFull.sizeL');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        const productData = await page.evaluate(() => {
            let productName = document.querySelector('div.detailArea > div.infoArea > div.headingArea > h1').textContent.trim();
            productName = productName.replace(/^\(.*?\)\s*\S*\s*/, '').trim();
            const productPrice = document.querySelector('#span_product_price_text').textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('div.RW > div.prdImg > div > a > img').src;
            const productDetailElements = document.querySelectorAll('#prdDetail > div > p img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetailSrc = productDetailElement.src;
                if (tempProductDetailSrc === 'https://gi.esmplus.com/do4kim/shopping/toyjjanggosi.jpg' || tempProductDetailSrc === 'https://gi.esmplus.com/do4kim/info_baesong.jpg' || tempProductDetailSrc === 'https://do4kim.openhost.cafe24.com/end/ft_1.jpg' || tempProductDetailSrc === 'https://do4kim.openhost.cafe24.com/end/ft_2.jpg' || tempProductDetailSrc === 'https://gi.esmplus.com/do4kim/kspage/brand.jpg' || tempProductDetailSrc === '//gi.esmplus.com/do4kim/shopping/toyjjanggosi.jpg' || tempProductDetailSrc === '//gi.esmplus.com/do4kim/info_baesong.jpg' || tempProductDetailSrc === '//gi.esmplus.com/do4kim/learningresourceskorea/LER_gosi.jpg' || tempProductDetailSrc === '//gi.esmplus.com/do4kim/learningresourceskorea/LER_BS.jpg') {
                    continue;
                }
                productDetail.push(productDetailElement.src);
            }
            const hasOption = document.querySelectorAll('table.options').length > 0;
            const productOptions = [];
            const productData = {
                productName,
                productPrice,
                productImage,
                productDetail,
                hasOption,
                productOptions
            };
            return productData;
        });
        if (!productData) {
            return false;
        }
        const { productName, productPrice, productImage, productDetail, hasOption, productOptions } = productData;
        const product = {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref: url,
            sellerID: 74
        };
        return product;
    } catch (error) {
        console.error(error);
        return false;
    }
}
