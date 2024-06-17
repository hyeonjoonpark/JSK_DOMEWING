const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
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
            const txtDescElement = document.querySelector('p.txtDesc');
            if (txtDescElement && txtDescElement.textContent.trim().includes('사라졌거나')) {
                return false;
            }
            const soldOutImage = document.querySelector('div.infoArea img[src="//img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif"]');
            if (soldOutImage) {
                return false;
            }
            const buyButton = document.querySelector('a.first');
            if (buyButton && buyButton.classList.contains('displaynone') && buyButton.textContent.trim().includes('구매하기')) {
                return false;
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
async function signIn(page, username, password) {
    const goToAttemptsResult = await goToAttempts(page, 'https://petbtob.co.kr/member/login.html', 'networkidle0');
    if (goToAttemptsResult === false) {
        return false;
    }
    try {
        await page.evaluate((username, password) => {
            document.querySelector('#member_id').value = username;
            document.querySelector('#member_passwd').value = password;
            document.querySelector('#contents > form > div > div > fieldset > a').click();
        }, username, password);
    } catch (error) {
        return false;
    }
    try {
        await page.waitForNavigation({ waitUntil: 'load', timeout: 1000 });
    } catch (error) {

    } finally {
        return true;
    }
}
async function goToAttempts(page, url, waitUntil, attempt = 0, maxAttempts = 3) {
    if (attempt >= maxAttempts) {
        return false;
    }
    try {
        await page.goto(url, { waitUntil });
        page.once('dialog', async dialog => {
            await dialog.accept();
        });
        return true;
    } catch (error) {
        return await goToAttempts(page, url, waitUntil, attempt++, maxAttempts);
    }
}
async function getProductOptions(page) {
    async function reloadSelects() {
        return page.$$('select.ProductOption0');
    }
    async function resetSelects() {
        const delBtn = await page.$('#option_box1_del');
        if (delBtn) {
            await delBtn.click();
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
    }
    async function reselectOptions(selects, selectedOptions) {
        for (let i = 0; i < selectedOptions.length; i++) {
            await selects[i].select(selectedOptions[i].value);
            await new Promise(resolve => setTimeout(resolve, 1000));
            if (i < selectedOptions.length - 1) {
                selects = await reloadSelects();
            }
        }
    }
    async function processSelectOptions(selects, currentDepth = 0, selectedOptions = [], productOptions = []) {
        if (currentDepth < selects.length) {
            const options = await selects[currentDepth].$$eval('option:not(:disabled)', opts =>
                opts.map(opt => ({ value: opt.value, text: opt.text }))
                    .filter(opt => opt.value !== '' && opt.value !== '*' && opt.value !== '**' && !opt.text.includes("품절"))
            );
            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];
                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([\+\-]?\d{1,3}(,\d{3})*원\)/g, "").trim()
                    ).join(", ");
                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        const matches = opt.text.match(/\(([\+\-]?\d{1,3}(,\d{3})*원)\)/);
                        return total + (matches ? parseInt(matches[1].replace(/,|원|\+/g, ''), 10) : 0);
                    }, 0);
                    productOptions.push(optionName);
                }
                await resetSelects();
                selects = await reloadSelects();
                if (currentDepth > 0) {
                    await reselectOptions(selects, selectedOptions);
                    selects = await reloadSelects();
                }
            }
        }
        return productOptions;
    }
    const selects = await reloadSelects();
    return processSelectOptions(selects);
}
