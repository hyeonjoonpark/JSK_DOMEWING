const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage(); //페이지 열고
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8')); //products 변수 선언
        const signInResult = await signIn(page, username, password); // 로그인 시도
        if (signInResult === false) { //로그인 실패 처리
            console.log(JSON.stringify('로그인 과정에서 오류가 발생했습니다.'));
            return;
        }
        const soldOutProductIds = []; //품절상품 담을 배열
        for (const product of products) { // 상품조회 반복
            const goToAttemptsResult = await goToAttempts(page, product.productHref, 'domcontentloaded'); //페이지 접속 3번 시도
            if (goToAttemptsResult === false) { // 3번 시도후 실패한다면
                soldOutProductIds.push(product.id); //품절상품 배열에 상품id값을 담아버림
                continue; // 그리고 종료.
            }
            const isValid = await validateProduct(page); // 유효상품 검사
            if (isValid === false) { // 유효하지 않을때
                soldOutProductIds.push(product.id); // 품절상품 배열에 상품id값을 감아버림
            }
        }
        console.log(JSON.stringify(soldOutProductIds));
        const sopFile = path.join(__dirname, 'pettob_result.json'); // 파일 경로 설정
        fs.writeFileSync(sopFile, JSON.stringify(soldOutProductIds), 'utf8'); // 파일에 문자열로 저장
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close(); // 창닫기.
    }
})();
async function validateProduct(page) { // 상품 품절 검증
    try {
        const soldOutPopupProduct = await checkPopupProduct(page);
        if (soldOutPopupProduct) {
            return false;
        }
        return await page.evaluate(() => {
            const soldOutElement = document.querySelector('#goods_spec > form > div.view_btn > span > button');
            if (soldOutElement) {
                return false;
            }
            const soldOutImage = document.querySelector('div.infoArea img[src="//img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif"]');
            if (soldOutImage) {
                return false;
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
async function checkPopupProduct(page) { // 팝업 창 검증
    let popupPage = false;

    page.on('dialog', async dialog => {
        if (dialog.type() === 'alert') {
            popupPage = true;
            await dialog.dismiss();
        }
    });
};
async function signIn(page, username, password) { //로그인 처리
    const goToAttemptsResult = await goToAttempts(page, 'https://pettob.co.kr/shop/main/intro_member.php?returnUrl=%2Fshop%2Fmain%2Findex.php', 'networkidle0');
    if (goToAttemptsResult === false) {
        return false;
    }
    try {
        await page.evaluate((username, password) => {
            document.querySelector('#login_frm > div:nth-child(3) > input').value = username;
            document.querySelector('#login_frm > div:nth-child(4) > input').value = password;
            document.querySelector('#login_frm > div:nth-child(5) > a.btn.btn-default.btn-lg.submit.btn_login').click();
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
async function goToAttempts(page, url, waitUntil, attempt = 0, maxAttempts = 3) { //로그인 시도 3회
    if (attempt >= maxAttempts) { //3번이 넘어가면 false처리
        return false;
    }
    try { //정상적 로그임처리
        await page.goto(url, { waitUntil });
        page.once('dialog', async dialog => {
            await dialog.accept();
        });
        return true;
    } catch (error) {
        return await goToAttempts(page, url, waitUntil, attempt++, maxAttempts);
    }
}
