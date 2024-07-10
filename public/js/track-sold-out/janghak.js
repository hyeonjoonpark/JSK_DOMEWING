const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { goToAttempts, signIn } = require('./trackwing-common');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const signInResult = await signIn(page, username, password, 'https://www.jhmungu.com/shop/login.php', 'div > div:nth-child(1) > form > div:nth-child(2) > input[type=text]:nth-child(1)', 'div > div:nth-child(1) > form > div:nth-child(2) > input.mt-2', 'div > div:nth-child(1) > form > div.form-group.row.text-center > div.col-12.col-md > button');
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
        const sopFile = path.join(__dirname, 'janghak_result.json');
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
            const txtDescElements = document.querySelectorAll('body > div.container.goods_detail_skin > div.g_skin_head.mt-lg-3.mb-3.mb-md-4.row.px-2.px-lg-0 > div.goods_info.col-12.col-lg.pl-lg-3 > div.d-flex.flex-wrap.justify-content-between.mb-3.pb-2.border-bottom > div.col-auto.d-flex > p span');
            for (const txtDescElement of txtDescElements) {
                const textContent = txtDescElement.textContent.trim();
                if (
                    textContent.includes('배송불가') ||
                    textContent.includes('취급안함') ||
                    textContent.includes('품절') ||
                    textContent.includes('미정') ||
                    textContent.includes('단종') ||
                    textContent.includes('온라인 판매금지') ||
                    textContent.includes('반품불가')
                ) {
                    return false;
                }
            }
            const OrderLimit = document.querySelector('div:nth-child(2) > span.text-danger.ml-3');
            if (OrderLimit) {
                const orderLimitText = OrderLimit.textContent.trim();
                const match = orderLimitText.match(/\d+/);
                if (match && parseInt(match[0]) > 1) {
                    return false;
                }
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
