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
        let index = 0;
        for (const url of urls) {
            let goToWithRepeatResult = null;
            if (index === 0) {
                goToWithRepeatResult = await goToWithRepeat(page, url, 0, 'networkidle0');
                index++;
            } else {
                goToWithRepeatResult = await goToWithRepeat(page, url, 0, 'domcontentloaded');
            }
            if (goToWithRepeatResult === false) {
                continue;
            }
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
async function goToWithRepeat(page, url, index, wiatUntilType) {
    try {
        await page.goto(url, { waitUntil: wiatUntilType });
        return true;
    } catch (error) {
        if (index < 3) {
            index++
            await goToWithRepeat(page, url, index, wiatUntilType);
        } else {
            return false;
        }
    }
}
async function signIn(page, username, password) {
    await goToWithRepeat(page, 'https://vitsonmro.com/mro/login.do', 0, 'networkidle0');
    await new Promise((page) => setTimeout(page, 3000));
    // await page.evaluate(() => {
    //     const isPopup = document.querySelector('#groobeeWrap');
    //     if (isPopup) {
    //         isPopup.style.display = 'none';
    //         document.querySelector('body > div.grbDim.grbLayer').style.display = 'none';
    //     }
    // });
    await page.type('#custId', username);
    await page.type('#custPw', password);
    await page.click('#loginForm > div > a:nth-child(3)');
    await page.waitForSelector('#wrap');
}
async function scrapeProduct(page, productHref) {
    try {
        const product = await page.evaluate((productHref) => {
            let productName = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.top_title_bar > h3').textContent.trim();
            const productStandard = document.querySelector('#table > tbody > tr:nth-child(2) > td:nth-child(2)').textContent.trim();
            productName += ' ' + productStandard;
            const productPrice = document.querySelector('#negoPrice').textContent.trim().replace(/[^\d]/g, '');
            const productImage = document.querySelector('body > div.container > div > div.content > div.wrap_deal > div.deal_view > div.deal_gallery > div.swiper-container.gallery-top.swiper-container-horizontal > div > div.swiper-slide.swiper-slide-active > img').src;
            const images = document.querySelectorAll('#detail_box > div > ul img');
            const productDetail = Array.from(images, img => {
                let src = img.getAttribute('src');
                return src;
            });
            const hasOption = false;
            const productOptions = [];
            return {
                productName: productName,
                productPrice: productPrice,
                productImage: productImage,
                productDetail: productDetail,
                hasOption: hasOption,
                productOptions: productOptions,
                productHref: productHref,
                sellerID: 13
            };
        }, productHref);
        return product;
    } catch (error) {
        return false;
    }
}
