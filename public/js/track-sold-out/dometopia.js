const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { goToAttempts, signIn } = require('./trackwing-common');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    await page.setViewport({
        width: 1920,
        height: 1080
    });
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const signInResult = await signIn(page, username, password, 'https://dometopia.com/member/login', '#userid', '#password', '#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
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
        const sopFile = path.join(__dirname, 'dometopia_result.json');
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
            const txtDescElement = document.querySelector('div.total.price.clearbox > span.button.bgred');
            if (txtDescElement && txtDescElement.textContent.trim().includes('품절')) {
                return false;
            }
            const txtCodeElement = document.querySelector('#info > div.goods_info.clearbox > form > div.container > table > tbody > tr:nth-child(1) > td:nth-child(2) > span');
            if (txtCodeElement) {
                const txtContent = txtCodeElement.textContent.trim();
                if (txtContent.includes('GKM') || txtContent.includes('GDR') || txtContent.includes('GDF') || txtContent.includes('AKS') || txtContent.includes('GKD') || txtContent.includes('ATS')) {
                    return false;
                }
            }
            const stockSelector = '#select_option_lay > div.quantity_box > table > tbody > tr:nth-child(2) > td';
            const availableStockElement = document.querySelector(stockSelector);
            // 요소의 존재 여부를 검사
            if (availableStockElement) {
                const availableStockElement = parseInt(availableStockElement.textContent.trim().replace(/[^\d]/g, ''));
                if (availableStockElement < 5) {
                    return false;
                }
            }
            const stockElement = document.querySelector('#info > div.goods_info.clearbox > form > div.container > table > tbody > tr:nth-child(4) > td');
            if (stockElement) {
                const stock = parseInt(stockElement.textContent.trim().replace(/[^\d]/g, ''));
                if (stock > 1) {
                    return false;
                }
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
