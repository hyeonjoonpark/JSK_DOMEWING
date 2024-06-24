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
        const signInResult = await signIn(page, username, password, 'https://www.domecall.net/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.login > button');
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
        const sopFile = path.join(__dirname, 'metaldiy_result.json');
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProductIds), 'utf8');
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function validateProduct(page) {
    let dialogAppeared = false;
    let dialogContainsOnline = false;
    page.once('dialog', async dialog => {
        try {
            const message = dialog.message();
            if (message.includes('온라인')) {
                dialogContainsOnline = true;
            }
            await dialog.accept();
        } catch (error) {
            console.error(error);
        } finally {
            dialogAppeared = true;
        }
    });
    try {
        const result = await page.evaluate(() => {
            const soldOutTextElement = document.querySelector('#frmView > div > div.btn > a');
            if (soldOutTextElement && soldOutTextElement.textContent.trim().includes('구매 불가')) {
                return false;
            }
            const soldOutButton = document.querySelector('#frmView > div > div.btn > a.skinbtn.point2.btn-add-order');
            if (soldOutButton && soldOutButton.src.includes('바로 구매')) {
                soldOutButton.click();
                return true;
            }
            return true;
        });
        // 잠시 대기하여 dialog 이벤트가 발생할 시간을 줍니다.
        await page.waitForTimeout(1000); // 필요에 따라 대기 시간을 조정하세요.
        // dialog 이벤트가 발생했고, 메시지에 '온라인'이 포함된 경우 false를 반환합니다.
        if (dialogAppeared && dialogContainsOnline) {
            return false;
        }
        return result;
    } catch (error) {
        return false;
    }
}

