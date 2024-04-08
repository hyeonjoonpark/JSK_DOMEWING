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
            try {
                await page.goto(url, { waitUntil: 'networkidle0' });
            } catch (error) {
                continue;
            }

            const product = await buildProduct(page, url);
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
async function signIn(page, username, password) {
    await page.goto('https://www.lalab2b.com/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#loginId', username);
    await page.type('#loginPwd', password);
    await page.click('#formLogin > div.login > button');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function buildProduct(page, productHref) {
    try {
        const isValid = await validateProduct(page);
        if (isValid === false) {
            return false;
        }
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption === true ? await getProductOptions(page) : [];
        const productPrice = await getProductPrice(page);
        if (productPrice < 1) {
            return false;
        }
        const productImage = await getProductImage(page);
        const productDetail = await getProductDetail(page);
        const product = {
            productName,
            productPrice,
            productImage,
            productDetail,
            hasOption,
            productOptions,
            productHref,
            sellerID: 47
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}
async function validateProduct(page) {
    return await page.evaluate(() => {
        if (document.querySelector('#frmView > div > div.btn > a > em')) {
            const isValidText = document.querySelector('#frmView > div > div.btn > a > em').textContent.trim();
            if (isValidText.includes('불가')) {
                return false;
            }
        }
    });
}
async function getProductPrice(page) {
    return await page.evaluate(() => {
        return parseInt(document.querySelector('li.price > div > strong').textContent.trim().replace(/[^\d]/g, ''), 10);
    });
}
async function getProductDetail(page) {
    return await page.evaluate(() => {
        const manualDivs = document.querySelectorAll('#detail > div.txt-manual');
        let imagesToKeep = [];

        manualDivs.forEach(div => {
            // 첫 번째 이미지를 제외한 나머지 이미지들을 선택
            const imgs = div.querySelectorAll('img:not(:nth-child(1))');
            imgs.forEach(img => {
                imagesToKeep.push(img.src);
            });
        });

        // forbiddenSrcs를 포함하지 않는 src들만 필터링
        const forbiddenSrces = ['dc_2in1sunshade_01.jpg', '860_GTlivinglife_intro_200306', "t002.jpg", "t001.jpg", "MI.jpg", "b002.jpg", "b001.jpg", "IO.jpg"];
        const filteredImages = imagesToKeep.filter(src => !forbiddenSrces.some(forbiddenSrc => src.includes(forbiddenSrc)));

        return filteredImages;
    });
}

async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('#mainImage > img').src;
    });
    return productImage;
}
async function getProductOptions(page) {
    return await page.evaluate(() => {
        const options = document.querySelectorAll('select.tune option');
        const productOptions = [];
        for (const option of options) {
            if (option.value !== '' &&
                option.value !== '*' &&
                option.value !== '**' &&
                !option.text.includes("품절") &&
                !option.text.includes("준수")) {
                let optionText = option.textContent.trim();
                if (optionText.includes(' : ')) {
                    optionText = optionText.split(' : ')[0];
                }

                // trim() 메서드의 결과를 바로 optionName에 할당합니다.
                const optionName = optionText.trim();
                const optionPrice = 0; // 가격을 설정하려면 이 부분을 수정해야 합니다.

                const productOption = {
                    optionName,
                    optionPrice
                };
                productOptions.push(productOption);
            }
        }
        return productOptions;
    });
}
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productName = document.querySelector('div.tit > h2').textContent.trim();
        return productName;
    });
    return productName;
}
async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.tune');
        if (selectElements.length > 0) {
            return true;
        }
        return false;
    });
}
