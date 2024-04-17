const fs = require('fs');
const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        // const [tempFilePath, username, password] = process.argv.slice(2);
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const urls = ['https://kwshop.co.kr/goods/view?no=39110', 'https://kwshop.co.kr/goods/view?no=38198'];
        const username = "jskorea2024";
        const password = "tjddlf88!@";
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            const product = await scrapeProduct(page, url);
            if (product === false) {
                continue;
            }
            products.push(...product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://kwshop.co.kr/member/login', { waitUntil: 'networkidle0' });
    await page.type('#userid', username);
    await page.type('#password', password);
    await page.click('#layout_config_full > div.login_top_conbx.mt20 > div > div > form > div > table > tbody > tr:nth-child(1) > td:nth-child(4) > input[type=image]');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {

        await page.goto(url, { waitUntil: 'networkidle0' });
        await scrollToDetail(page);
        const productDetail = await page.evaluate(() => {
            const productDetailElements = document.querySelectorAll('div.goods_description.mt10 img');
            if (productDetailElements.length < 1) {
                return false;
            }
            const productDetail = [];
            for (const productDetailElement of productDetailElements) {
                const tempProductDetail = productDetailElement.src;
                productDetail.push(tempProductDetail);
            }
            return productDetail;
        });
        // if (productDetail === false) {
        //     return false;
        // }
        const productImage = await page.evaluate(() => {
            const productImageElement = document.querySelector('#goods_thumbs > ul > li:nth-child(1) > div.slides_container.hide > a:nth-child(1) > img');
            if (!productImageElement) {
                return false;
            }
            const productImage = productImageElement.src;
            return productImage;
        });
        const products = await page.evaluate((productImage, productDetail, url) => {
            const productElements = document.querySelectorAll('div.gd_opt_t');
            if (!productElements) {
                return false;
            }
            const products = [];
            for (const productElement of productElements) {
                // 상품 검증: 주문 갯수 1개인지 아닌지, 용산/인천 재고수 10개 이상인지.
                const minQuantityElement = productElement.querySelector('div.gd_opt_c.gd_opt_ea > div > table > tbody > tr:nth-child(1) > td:nth-child(1) > input[type=text]');
                if (!minQuantityElement) {
                    continue;
                }
                const minQuantity = parseInt(minQuantityElement.value);
                if (minQuantity > 1) {
                    continue;
                }
                // 상품명
                const productNameElement = productElement.querySelector('div.gd_opt_c.gd_opt_name > p > span.goods_name > a');
                if (!productNameElement) {
                    continue;
                }
                const productName = productNameElement.textContent.trim();
                // 상품 가격
                const productPriceElement = productElement.querySelector('div.gd_opt_c.gd_opt_name > div > div > span');
                if (!productPriceElement) continue;  // 가격 정보가 없는 경우 건너뜁니다

                const productPriceText = productPriceElement.textContent.trim().replace(/[^\d]/g, '');
                const productPrice = parseInt(productPriceText, 10);
                if (isNaN(productPrice) || productPrice < 1) {
                    continue;  // productPrice가 NaN이거나 1 이하인 경우 이 상품을 건너뜁니다
                }
                const product = {
                    productName,
                    productPrice,
                    productImage,
                    productDetail,
                    productHref: url,
                    sellerID: 62,
                    hasOption: 'N',
                    productOptions: []
                };
                products.push(product);
            }
            return products;
        }, productImage, productDetail, url);
        if (products === false) {
            return false;
        }
        return products;
    } catch (error) {
        console.error('Error in scrapeProduct:', error);
        return false;
    }
}
async function scrollToDetail(page) {
    await page.evaluate(async () => {
        const distance = 100;
        const scrollInterval = 5;
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.querySelector('#goods_view > div.set_preload > div:nth-child(1) > div.goods_description.mt10');
            const prdInfoElement = document.querySelector('#goods_view > div.set_preload > div:nth-child(4)');
            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                break;
            } else {
                window.scrollBy(0, distance);
            }

            await new Promise(resolve => setTimeout(resolve, scrollInterval));
        }
    });
}
