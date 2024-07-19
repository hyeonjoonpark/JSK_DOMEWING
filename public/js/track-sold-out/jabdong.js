const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { goToAttempts, signIn } = require('./trackwing-common');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const signInResult = await signIn(page, username, password, 'https://jabdong.co.kr/member/login.html', '#member_id', '#member_passwd', 'div.login > fieldset > a img');
        if (signInResult === false) {
            console.log(JSON.stringify('로그인 과정에서 오류가 발생했습니다.'));
            return;
        }
        const soldOutProductIds = [];
        for (const product of products) {
            let dialogAppeared = false;
            page.once('dialog', async dialog => {
                try {
                    await dialog.accept();
                } catch (error) { } finally {
                    dialogAppeared = true;
                }
            });
            const goToAttemptsResult = await goToAttempts(page, product.productHref, 'domcontentloaded');
            if (goToAttemptsResult === false) {
                soldOutProductIds.push(product.id);
                continue;
            }
            const isValid = await validateProduct(page);
            if (isValid === false) {
                soldOutProductIds.push(product.id);
            }
        }
        const sopFile = path.join(__dirname, 'jabdong_result.json');
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProductIds), 'utf8');
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function validateProduct(page) {
    try {
        const isValidProduct = await page.evaluate(() => {
            const minOrderQuantity = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.guideArea > p');
            if (minOrderQuantity) {
                const textContent = minOrderQuantity.textContent.trim();
                if (!textContent.includes('최소주문수량 1개 이상')) {
                    return false;
                }
            }
            const soldOutSpanElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.xans-element-.xans-product.xans-product-action > div.btnArea > span');
            if (soldOutSpanElement) {
                const isSoldOut = !soldOutSpanElement.classList.contains('displaynone'); //데이터는 항상 존재해서 display 여부로 품절 파악
                if (isSoldOut) {
                    return false
                }
            }
            return true;
        });
        return isValidProduct;
    } catch (error) {
        return false;
    }
}
