// Puppeteer 모듈을 불러옵니다.
const puppeteer = require('puppeteer');

// 비동기 함수를 시작합니다. 이 함수는 전체 스크립트의 메인 로직을 담당합니다.
(async () => {
    // Puppeteer로 브라우저 인스턴스를 시작합니다. headless 모드를 비활성화하여 브라우저 UI가 보이게 합니다.
    const browser = await puppeteer.launch({ headless: false });
    // 새로운 페이지(탭)을 생성합니다.
    const page = await browser.newPage();
    try {
        // 명령줄 인수에서 추가 데이터를 가져옵니다. 여기서는 임시 파일 경로, 사용자 이름, 비밀번호를 예상합니다.
        const args = process.argv.slice(2);
        const [tempFilePath, username, password] = args;
        // 파일 시스템(fs) 모듈을 사용하여 URL 목록이 담긴 임시 파일을 읽어들입니다.
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        // 사용자를 로그인 시키는 함수를 호출합니다.
        await signIn(page, username, password);
        // 스크래핑된 제품 정보를 저장할 배열을 초기화합니다.
        const products = [];
        // 읽어들인 URL들을 순회하면서 각 제품 페이지에 접근합니다.
        for (const url of urls) {
            // 페이지 이동에 실패할 경우 재시도하는 함수를 호출합니다.
            const navigateWithRetryResult = await navigateWithRetry(page, url);
            // 페이지 이동에 실패했다면, 다음 URL로 넘어갑니다.
            if (navigateWithRetryResult === false) {
                continue;
            }
            // 현재 URL에서 제품 정보를 스크래핑하는 함수를 호출합니다.
            const product = await scrapeProduct(page, url);
            // 스크래핑에 실패하거나 제품 정보가 없으면 다음 URL로 넘어갑니다.
            if (product === false || product === null) {
                continue;
            }
            // 성공적으로 스크래핑된 제품 정보를 배열에 추가합니다.
            products.push(product);
        }
        // 최종적으로 스크래핑된 모든 제품 정보를 콘솔에 출력합니다.
        console.log(JSON.stringify(products));
    } catch (error) {
        // 에러가 발생했을 경우 에러 메시지를 콘솔에 출력합니다.
        console.error('Error occurred:', error);
    } finally {
        // 모든 작업이 완료되면, 브라우저 인스턴스를 닫습니다.
        await browser.close();
    }
})();

// 주어진 URL로 페이지를 이동하고, 필요한 경우에는 재시도하는 함수입니다.
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) { // 주어진 시도 횟수만큼 반복합니다.
        try {
            // 페이지를 주어진 URL로 이동시킵니다. 'domcontentloaded' 이벤트가 발생할 때까지 기다립니다.
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            return true; // 페이지 이동에 성공하면 true를 반환합니다.
        } catch (error) {
            // 페이지 이동에 실패하면 지정된 지연 시간만큼 기다린 후 재시도합니다.
            if (i < attempts - 1) {
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    return false; // 모든 시도가 실패하면 false를 반환합니다.
}

// 사용자 로그인을 처리하는 함수입니다.
async function signIn(page, username, password) {
    // 로그인 페이지로 이동합니다. 페이지의 모든 네트워크 요청이 완료될 때까지 기다립니다.
    await page.goto('https://campingmoon.co.kr/member/login.html', { waitUntil: 'networkidle0' });
    // 사용자 이름 입력 필드에 값을 입력합니다. #member_id는 사용자 이름 입력 필드의 CSS 선택자입니다.
    await page.type('#member_id', username);
    // 비밀번호 입력 필드에 값을 입력합니다. #member_passwd는 비밀번호 입력 필드의 CSS 선택자입니다.
    await page.type('#member_passwd', password);
    // 로그인 버튼을 클릭합니다. 이 예시에서는 <img> 태그가 클릭 트리거로 사용되고 있습니다.
    await page.click('#loginarea > div > div.mlogin > fieldset > ul.logbtn > li > a > img');
    // 로그인 결과 페이지로의 네비게이션이 완료될 때까지 기다립니다.
    await page.waitForNavigation();
}


async function scrapeProduct(page, productHref) {
    try {
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption ? await getProductOptions(page) : [];
        const productPrice = hasOption ? 0 : await page.evaluate(() => {
            const productPrice = document.querySelector('strong.total_price').textContent.trim().replace(/[^\d]/g, '');
            return productPrice;
        });
        const productImage = await getProductImage(page);
        const productDetail = await getProductDetail(page);
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 29
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}
async function getProductDetail(page) {
    return await page.evaluate(() => {
        const manualDivs = document.querySelectorAll('#detail > div.txt-manual');
        let imagesToKeep = [];

        manualDivs.forEach(div => {
            // 첫 번째 이미지를 제외한 나머지 이미지들을 선택
            const imgs = div.querySelectorAll('img:not(:nth-child(1))');
            imgs.forEach(img => {
                imagesToKeep.push(img.src);
            });
        });

        // forbiddenSrcs를 포함하지 않는 src들만 필터링
        const forbiddenSrces = ['dc_2in1sunshade_01.jpg', '860_GTlivinglife_intro_200306'];
        const filteredImages = imagesToKeep.filter(src => !forbiddenSrces.some(forbiddenSrc => src.includes(forbiddenSrc)));

        return filteredImages;
    });
}

async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('#mainImage > img').src;
    });
    return productImage;
}
async function getProductOptions(page) {
    async function reloadSelects() {
        return await page.$$('select.tune');
    }
    async function resetSelects() {
        const delBtn = await page.$('div.del > button');
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
                opts.map(opt => ({ value: opt.value, text: opt.text })).filter(opt => opt.value !== '')
            );
            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000));
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];
                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = "";
                    newSelectedOptions.forEach(opt => {
                        let optText = opt.text;
                        if (optText.includes(' : ')) {
                            optText = optText.split(' : ')[0];
                        }
                        optionName = optionName.length > 0 ? `${optionName}, ${optText}` : optText;
                        optionName = optionName.replace(/\s*\+\d{1,3}(,\d{3})*원/g, "");
                    });
                    const optionPrice = await page.$eval('strong.total_price', el => el.textContent.trim().replace(/[^\d]/g, ''));
                    const productOption = { optionName, optionPrice };
                    productOptions.push(productOption);
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
    let selects = await reloadSelects();
    const productOptions = await processSelectOptions(selects);
    return productOptions;
}
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const productName = document.querySelector('#frmView > div > div.goods-header > div.top > div > h2').textContent.trim();
        return productName;
    });
    return productName;
}
async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.tune');
        if (selectElements.length > 0) {
            return true;
        }
        return false;
    });
}
