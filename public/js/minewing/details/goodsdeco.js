
const fs = require('fs');
const puppeteer = require('puppeteer');
const { goToAttempts, signIn, scrollDown } = require('../common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: false, args: ['--start-maximized'] });
    const page = await browser.newPage();
    await page.setViewport({ 'width': 1900, 'height': 1080 });
    let [tempFilePath, username, password] = process.argv.slice(2);
    await page.setDefaultNavigationTimeout(0);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
    const products = [];
    let exitType = 0;
    try {
        await signIn(page, username, password, 'https://goodsdeco.com/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.member_login_box > div.login_input_sec > button');
        for (const url of urls) {
            const goToAttemptsResult = await goToAttempts(page, url, 'domcontentloaded');
            if (!goToAttemptsResult) {
                continue;
            }
            const product = await buildProduct(page, url);

            if (!product) {
                continue;
            }
            products.push(product);

        }
    } catch (error) {
        exitType = 1;
        errMsg = error;
    } finally {
        if (exitType === 0) {
            console.log(JSON.stringify(products));
        } else if (exitType === 1) {
            if (products.length > 0) {
                console.log(JSON.stringify(products));
            } else {
                console.error(errMsg);
            }
        }
        process.exit(exitType)
    }
})();
async function buildProduct(page, productHref) {
    const productName = await getProductName(page);
    if (!productName) {
        return false;
    }
    const productPrice = await getproductPrice(page);
    if (!productPrice) {
        return false;
    }
    const productImage = await getproductImage(page);
    if (!productImage) {
        return false;
    }
    const productDetail = await getproductDetail(page);
    if (!productDetail) {
        return false;
    }
    const productOptions = await getproductOptions(page);
    if (productOptions === false) return false;
    const hasOption = productOptions.length > 0;

    return {
        productName,
        productPrice,
        productImage,
        productDetail,
        hasOption,
        productOptions,
        productHref,
        sellerID: 77
    };
}
async function getProductName(page) {
    return await page.evaluate(() => {
        const productNameElement = document.querySelector('#frmView > div > div > div.item_detail_tit > h3');
        if (!productNameElement) {
            return false;
        }
        return productNameElement.textContent.trim();
    });
}
async function getproductPrice(page) {
    return await page.evaluate(() => {
        const productPriceElement = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd');
        if (!productPriceElement) {
            return false;
        }
        return productPriceElement.textContent.replace(/[^0-9]/g, '').trim();
    });
}
async function getproductImage(page) {
    return await page.evaluate(() => {
        const productImageElement = document.querySelector('#mainImage > img');
        if (!productImageElement) {
            return false;
        }
        return productImageElement.src;
    });
}
async function getproductDetail(page) {
    return await page.evaluate(() => {

        let productDetail = [];

        const productDetailElementsImgCheckValid = document.querySelectorAll('#mainImage > img');
        const extraImg = document.querySelectorAll('#detail > div.detail_cont > div > div.txt-manual > div > img');
        const productDetailElements = document.querySelector('#contents > div > div.content_box > div.item_photo_info_sec > div + #frmView');
        if (!productDetailElements) {
            return false;
        }
        for (const imgs of productDetailElementsImgCheckValid) {
            const productDetailSrc = imgs.src;
            if (productDetailSrc)
                productDetail.push(productDetailSrc);
        }
        for (const imgs of extraImg) {
            const productDetailSrc = imgs.src;
            if (productDetailSrc)
                productDetail.push(productDetailSrc);

        }
        if (productDetail.length < 1) {
            return false;
        }
        return productDetail;
    });
}
async function getproductOptions(page) {
    return await page.evaluate(() => {
        const productOptionElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl > dd > select option');
        const productOptionsObjec = Array.from(productOptionElements)
            .map(poe => poe)
            .filter(option => !option.getAttribute('disabled'));
        const productOptions = productOptionsObjec.map((val) => val.textContent.trim());
        if (productOptionElements.length > 0 && productOptions.length < 1) {
            return false;
        }
        return productOptions;
    });
}
