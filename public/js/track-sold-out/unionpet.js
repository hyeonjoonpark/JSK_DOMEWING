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
        const signInResult = await signIn(page, username, password, 'https://www.unionpet.co.kr/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.member_login_box > div.login_input_sec > button');
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
        const sopFile = path.join(__dirname, 'unionpet_result.json');
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
            function getTotalStock() {
                const dlElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > dl');
                let totalStock = 0;
                dlElements.forEach(dl => {
                    const textContent = dl.textContent.trim();
                    if (textContent.includes('상품재고')) {
                        const stockMatch = textContent.match(/[\d,]+/);
                        if (stockMatch) {
                            const stock = parseInt(stockMatch[0].replace(/,/g, ''), 10);
                            totalStock += stock;
                        }
                    }
                });
                return totalStock;
            }
            const productStatusElement = document.querySelector('#frmView > div > div > div.item_detail_tit > h3 > font > b');
            if (productStatusElement) {
                const textContent = productStatusElement.textContent.trim();
                if (textContent.includes('임박1+1') || textContent.includes('유통기한') || (textContent.includes('임박') && textContent.includes('할인'))) {
                    return false;
                }
            }
            const stock = getTotalStock();
            if (stock < 5) {
                return false;
            }
            const isOfflineElement = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd > strong');
            if (isOfflineElement) {
                const textContent = isOfflineElement.textContent.trim();
                if (textContent.includes('오프라인 전용') || textContent.includes('오프리인 전용')) {//오타있음
                    return false;
                }
            }
            const isSoldOutElement = document.querySelector('#frmView > div > div > div.btn_choice_box.btn_restock_box > button');
            if (isSoldOutElement) {
                const isSoldOut = isSoldOutElement.textContent.trim().includes('품절');
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
