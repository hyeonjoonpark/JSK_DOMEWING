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
    await page.goto('https://www.lanmart.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle0' });
    await page.type('#form > table > tbody > tr:nth-child(1) > td:nth-child(1) > input', username);
    await page.type('#form > table > tbody > tr:nth-child(2) > td > input', password);
    await page.click('#form > table > tbody > tr:nth-child(1) > td.noline > input');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function scrapeProduct(page, url) {
    try {
        await page.goto(url, { waitUntil: 'networkidle0' });
        await scrollToDetail(page);

        const productDetail = await page.evaluate(() => {
            const excludedImageUrls = [
                "http://www.lanmart.co.kr/shop/data/sphtml_button.png",
                "http://www.lanmart.co.kr/shop/data/sphtml_button2.png",
                "http://www.lanmart.co.kr/shop/product/Product_notice860.jpg",
                "http://www.kwshop.co.kr/contents/FAQ/FAQ_top.jpg",
                "http://www.kwshop.co.kr/ariel/USB/KW-825/KW-825_01.jpg"
            ];
            const productDetailElements = document.querySelectorAll('#contents > center > center img');
            if (productDetailElements.length < 1) {
                return false; // 상세 이미지가 없으면 false 반환
            }
            const productDetails = Array.from(productDetailElements).filter(el => !excludedImageUrls.includes(el.src)).map(el => el.src);
            return productDetails.length > 0 ? productDetails : false; // 제외된 이미지 후 남은 이미지가 없으면 false 반환
        });
        if (!productDetail) {
            return false; // 제외된 이미지 후 남은 상세 이미지가 없으면 처리 중단
        }

        const productImage = await page.evaluate(() => {
            const productImageElement = document.querySelector('#objImg');
            return productImageElement ? productImageElement.src : false;
        });
        if (!productImage) {
            return false; // 제품 이미지가 없으면 처리 중단
        }

        const productName = await page.evaluate(() => {
            const productNameElement = document.querySelector('#goods_spec > form > div.price > table > tbody > tr:nth-child(1) > td > span'); // 실제 웹사이트에 맞는 셀렉터 사용
            return productNameElement ? productNameElement.textContent.trim().replace(/\[.*?\]/g, '').replace(/\(.*?\)/g, '') : false;
        });
        if (!productName) {
            return false;
        }

        const productPrice = await page.evaluate(() => {
            const productPriceElement = document.querySelector('[id="price"]'); // 실제 웹사이트에 맞는 셀렉터 사용
            const productPriceText = productPriceElement ? productPriceElement.textContent.trim().replace(/[^\d]/g, '') : '0';
            return parseInt(productPriceText, 10);
        });
        if (isNaN(productPrice) || productPrice < 1) {
            return false; // 가격이 유효하지 않거나 1 이하면 처리 중단
        }

        return {
            productName,
            productPrice,
            productImage,
            productDetail,
            productHref: url,
            sellerID: 63,
            hasOption: 'N',
            productOptions: []
        };
    } catch (error) {
        console.error('Error in scrapeProduct:', error);
        return false;
    }
}


async function scrollToDetail(page) {
    await page.evaluate(async () => {
        const distance = 50;
        const scrollInterval = 5;
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.querySelector('#content > div.indiv > div.goodsInfo > div:nth-child(2) > table > tbody > tr');
            const prdInfoElement = document.querySelector('div.delivery_wrap.clear');
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
