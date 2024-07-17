const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { goToAttempts, signIn } = require('./trackwing-common');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const signInResult = await signIn(page, username, password, 'https://pettory.com/member/login.html', '#member_id', '#member_passwd', 'div > div.login > fieldset > a');
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
            if (isValid === false || dialogAppeared === true) {
                soldOutProductIds.push(product.id);
            }
        }
        const sopFile = path.join(__dirname, 'pettory_result.json');
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProductIds), 'utf8');
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function validateProduct(page) {
    try {
        return await page.evaluate(() => {
            const txtDescElement = document.querySelector('p.txtDesc');
            if (txtDescElement && txtDescElement.textContent.trim().includes('사라졌거나')) {
                return false;
            }
            const soldOutImage = document.querySelector('div.infoArea img[src="//img.echosting.cafe24.com/design/common/icon_sellout.gif"]');
            if (soldOutImage) {
                return false;
            }
            const buyButton = document.querySelector('a.first');
            if (buyButton && buyButton.classList.contains('displaynone') && buyButton.textContent.trim().includes('구매하기')) {
                return false;
            }
            const orderLimitElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-detail > div.detailArea > div.infoArea > div.guideArea > p');
            if (orderLimitElement) {
                const orderLimitText = orderLimitElement.textContent.trim();
                const minimumOrderMatch = orderLimitText.match(/최소주문수량\s*(\d+)\s*개 이상/);
                if (minimumOrderMatch && parseInt(minimumOrderMatch[1], 10) > 1) {
                    return false;
                }
            }
            return true
        });
    } catch (error) {
        return false;
    }
}
