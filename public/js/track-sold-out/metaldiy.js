const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const signInResult = await signIn(page, username, password);
        if (signInResult === false) {
            console.log(JSON.stringify('로그인 과정에서 오류가 발생했습니다.'));
            return;
        }
        const soldOutProductIds = [];
        for (const product of products) {
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
        const sopFile = path.join(__dirname, 'petbtob_result.json');
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
            if (soldOutTextElement && soldOutTextElement.textContent.trim().includes('품절')) {
                return false;
            }
            const soldOutButton = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_title > h2 > span.goods_restock > a > img');
            if (soldOutButton && soldOutButton.scr.includes('/images/shop/option_btn_msg.gif')) {
                return false;
            }
            const buyButton = document.querySelector('#container > div.container.wrapper_fix > div > div.goods_info > div.right > div > a:nth-child(2) > img');
            if (buyButton && buyButton.src.includes('/images/shop/goods_btn_big_buy.gif') && buyButton.alt.trim().includes('바로구매')) {
                return false;
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
async function signIn(page, username, password) {
    const goToAttemptsResult = await goToAttempts(page, 'https://www.metaldiy.com/login/popupLogin.do?popupYn=Y', 'networkidle0');
    if (goToAttemptsResult === false) {
        return false;
    }
    try {
        await page.evaluate((username, password) => {
            document.querySelector('#loginId').value = username;
            document.querySelector('#loginPw').value = password;
            document.querySelector('#wrapper > div > div.popup_login > div.login_box > fieldset > div > div.login_btn > input[type=image]').click();
        }, username, password);
    } catch (error) {
        return false;
    }
    try {
        await page.waitForNavigation({ waitUntil: 'load', timeout: 1000 });
    } catch (error) {
        return true;
    }
}
async function goToAttempts(page, url, waitUntil, attempt = 0, maxAttempts = 3) {
    if (attempt >= maxAttempts) {
        return false;
    }
    try {
        await page.goto(url, { waitUntil });
        return true;
    } catch (error) {
        return await goToAttempts(page, url, waitUntil, attempt++, maxAttempts);
    }
}
