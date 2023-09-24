const puppeteer = require('puppeteer');
const mysql = require('mysql2/promise');
const path = require('path');

(async () => {
    const browser = await puppeteer.launch({ headless: true, ignoreDefaultArgs: ['--enable-automation'] });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [username, password, formedExcel] = args;

        await page.goto('https://ownerclan.com/vender/');
        const frame = await page.frames().find(frame => frame.name() === 'vmainframe');
        await frame.type('input[name="id"]', username);
        await frame.type('input[name="passwd"]', password);
        await frame.click('input[type="submit"]');
        await frame.waitForNavigation();
        await page.goto('https://ownerclan.com/vender/product_register_bulk.php');
        await page.waitForSelector('#form1 > table > tbody > tr:nth-child(1) > td > div:nth-child(9) > div.innerDiv.bottom > input[type=button]:nth-child(2)');
        await page.click('#form1 > table > tbody > tr:nth-child(1) > td > div:nth-child(9) > div.innerDiv.bottom > input[type=button]:nth-child(2)');
        const newPagePromise = new Promise(x => browser.once('targetcreated', target => x(target.page())));
        const newPage = await newPagePromise;
        const filePath = path.join(__dirname, '..', '..', 'assets', 'excel', 'formed', formedExcel);
        console.log(filePath);
        // 파일 업로드를 위한 파일 경로를 설정합니다.


        // 파일 업로드 input 엘리먼트를 선택하고 파일을 업로드합니다.
        await newPage.waitForSelector('#xls_file');
        const elementHandle = await newPage.$("#xls_file");
        await elementHandle.uploadFile(filePath);
        await newPage.click('#btn_upload');
        const returnMsg = await newPage.on('dialog', async (dialog) => {
            if (dialog.type() === 'alert') {
                const returnMsg = dialog.message();
                await dialog.accept(); // 확인 버튼 클릭
                return returnMsg;
            }
        });
        await newPage.waitForTimeout(5000); // 예: 5초 동안 대기

        // await newPage.click('#btn_accept'); // "btn_accept" 버튼 클릭
        await newPage.evaluate(() => {
            //20170426 경고키워드 발생시 체크안하면 진행불가
            var testvalue = 0;
            var checked = []
            $("input[name='testcheck[]']:not(:checked)").each(function () {
                alert('알림사항을 확인하시고 체크박스에 체크하여 동의하면 정상적으로 등록 가능합니다.');
                testvalue = 1;
                return false;
            });

            if (testvalue == 1) {
                return;
            }


            if (lastResult == false) {
                alert("실행한 데이터가 온전하지 않아 적용할 수 없습니다");
                return;
            } else {
                var myVar = window.frames["doFrame"].window;
                var sfile = myVar.filepath;

                window.opener.document.form1.categoryRecommend.value = $("input[name='categoryRecommend']:checked").val();
                window.opener.setAccept(true, sfile);
                self.close();
            }
        });

        await page.evaluate(() => {
            var frm = document.getElementById('form1');
            frm.submit();
        });
        await page.on('dialog', async dialog => {
            const returnMsg = dialog.message();
            //get alert message
            console.log(dialog.message());
            //accept alert
            await dialog.accept();
            return returnMsg;
        });
        console.log(JSON.stringify(returnMsg));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();