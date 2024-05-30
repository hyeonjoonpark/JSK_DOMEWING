const puppeteer = require('puppeteer');
const fs = require('fs');
const { getOptionName } = require('./extract_product_option');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        await signIn(page, username, password);
        const maxAttempts = 3;
        const soldOutProducts = [];
        for (const product of products) {
            const optionName = getOptionName(product.productDetail);
            const enterResult = await enterProductPage(page, product.productHref, maxAttempts, 0);
            if (enterResult === false) {
                const soldOutProduct = {
                    id: product.id
                };
                soldOutProducts.push(soldOutProduct);
                continue;
            }
            const ivp = await isValidProduct(page, product.productHref, maxAttempts, 0);
            if (ivp === false) {
                const soldOutProduct = {
                    id: product.id
                };
                soldOutProducts.push(soldOutProduct);
            }
        }
        // 추가된 부분: soldOutProducts 배열 확인 및 에러 메시지 출력
        if (soldOutProducts.length === 0) {
            console.error('Error: No products were marked as sold out.');
        } else {
            console.log(JSON.stringify(soldOutProducts));
        }
        // 여기까지 추가된 부분
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();

async function enterProductPage(page, productHref, maxAttempts, attempt) {
    try {
        await page.goto(productHref, { waitUntil: 'domcontentloaded' });
        return true;
    } catch (error) {
        if (attempt >= maxAttempts) {
            return false;
        }
        return await enterProductPage(page, productHref, maxAttempts, attempt + 1);
    }
}

async function signIn(page, username, password) {
    await page.goto('https://petbtob.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    await page.evaluate((username, password) => {
        document.querySelector('#member_id').value = username;
        document.querySelector('#member_passwd').value = password;
        document.querySelector('#contents > form > div > div > fieldset > a').click();
    }, username, password);
    await page.waitForNavigation({ waitUntil: 'load' });
}

async function isValidProduct(page, productHref, maxAttempts, attempt) {
    try {
        return await page.evaluate(() => {
            const soldOutSelector = '#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > span.icon > img';
            const soldOutImage = document.querySelector(soldOutSelector);
            if (soldOutImage && soldOutImage.src.includes("img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif")) {
                return false;
            }
            const errorImage = document.querySelector('img[src="//img.echosting.cafe24.com/ec/image_admin/img_404.png"]');
            if (errorImage) {
                return 'error';
            }
            return true;
        });
    } catch (error) {
        return 'error';
    }
}

// 수정된 enterProductPage 함수
async function enterProductPage(page, productHref, maxAttempts, attempt) {
    try {
        await page.goto(productHref, { waitUntil: 'domcontentloaded' });
        const isValid = await isValidProduct(page);
        if (isValid === 'error') {
            if (attempt >= maxAttempts) {
                return false;
            }
            return await enterProductPage(page, productHref, maxAttempts, attempt + 1);
        }
        return isValid;
    } catch (error) {
        if (attempt >= maxAttempts) {
            return false;
        }
        return await enterProductPage(page, productHref, maxAttempts, attempt + 1);
    }
}
