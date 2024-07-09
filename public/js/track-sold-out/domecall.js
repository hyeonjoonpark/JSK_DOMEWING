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
        const sopFile = path.join(__dirname, 'domecall_result.json');
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProductIds), 'utf8');
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function validateProduct(page) {
    let dialogAppeared = false;
    try {
        page.once('dialog', async dialog => {
            try {
                await dialog.accept();
            } catch (error) { } finally {
                dialogAppeared = true;
            }
        });
        const isProductValid = await page.evaluate(() => {
            const soldOutTextElement = document.querySelector('#frmView > div > div.btn > a');
            if (soldOutTextElement && soldOutTextElement.textContent.trim().includes('구매 불가')) {
                return false;
            }
            const soldOutButton = document.querySelector('#frmView > div > div.btn > a.skinbtn.point2.btn-add-order');
            if (soldOutButton && soldOutButton.textContent.includes('바로 구매')) {
                soldOutButton.click();
                return true;
            }
            return true;
        });
        await Promise.race([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            new Promise(resolve => {
                setTimeout(resolve, 5000);
            })
        ]);
        if (dialogAppeared) {
            return false;
        }
        return isProductValid;
    } catch (error) {
        return false;
    }
}
