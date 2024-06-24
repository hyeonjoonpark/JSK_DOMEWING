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
        const signInResult = await signIn(page, username, password, 'https://www.metaldiy.com/login/popupLogin.do?popupYn=Y', '#loginId', '#loginPw', '#wrapper > div > div.popup_login > div.login_box > fieldset > div > div.login_btn > input[type=image]');
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
            if (isValid === false || dialogAppeared === true) { // 유효하지 않을때
                soldOutProductIds.push(product.id); // 품절상품 배열에 상품id값을 감아버림
            }
        }
        const sopFile = path.join(__dirname, 'metaldiy_result.json');
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
            const soldOutTextElement = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_title > h2 > span:nth-child(2)');
            if (soldOutTextElement && (soldOutTextElement.textContent.trim().includes('품절') || soldOutTextElement.textContent.trim().includes('단종'))) {
                return false;
            }
            const soldOutButton = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_title > h2 > span.goods_restock > a > img');
            if (soldOutButton && soldOutButton.src.includes('/images/shop/option_btn_msg.gif')) {
                return false;
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
