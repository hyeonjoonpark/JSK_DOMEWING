const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { goToAttempts, signIn } = require('./trackwing-common');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage(); //페이지 열고
    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const products = JSON.parse(fs.readFileSync(tempFilePath, 'utf8')); //products 변수 선언
        const signInResult = await signIn(page, username, password, 'https://pettob.co.kr/shop/main/intro_member.php?returnUrl=%2Fshop%2Fmain%2Findex.php', '#login_frm > div:nth-child(3) > input', '#login_frm > div:nth-child(4) > input', '#login_frm > div:nth-child(5) > a.btn.btn-default.btn-lg.submit.btn_login');
        if (signInResult === false) { //로그인 실패 처리
            console.log(JSON.stringify('로그인 과정에서 오류가 발생했습니다.'));
            return;
        }
        const soldOutProductIds = []; //품절상품 담을 배열
        for (const product of products) { // 상품조회 반복
            let dialogAppeared = false;
            page.once('dialog', async dialog => {
                try {
                    await dialog.accept();
                } catch (error) { } finally {
                    dialogAppeared = true;
                }
            });
            const goToAttemptsResult = await goToAttempts(page, product.productHref, 'domcontentloaded'); //페이지 접속 3번 시도
            if (goToAttemptsResult === false) { // 3번 시도후 실패한다면
                soldOutProductIds.push(product.id); //품절상품 배열에 상품id값을 담아버림
                continue; // 그리고 종료.
            }
            const isValid = await validateProduct(page); // 유효상품 검사
            if (isValid === false || dialogAppeared === true) { // 유효하지 않을때
                soldOutProductIds.push(product.id); // 품절상품 배열에 상품id값을 감아버림
            }
        }
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
        return await page.evaluate(() => {
            const soldOutElement = document.querySelector('#goods_spec > form > div.view_btn > span > button');
            if (soldOutElement) {
                return false;
            }
            const expirationDateElement = document.querySelector('strong.label.label-success');
            if (expirationDateElement) {
                return false;
            }
            return true;
        });
    } catch (error) {
        return false;
    }
}
