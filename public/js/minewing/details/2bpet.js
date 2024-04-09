const puppeteer = require('puppeteer');
const fs = require('fs');
const { timeout } = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        // const [tempFilePath, username, password] = args;
        // const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const urls = ['https://www.2bpet.co.kr/product/content.asp?guid=207126&cate=14423&params='];
        const username = "jskorea2024";
        const password = "tjddlf88!@";
        await signIn(page, username, password);
        const products = [];
        for (const url of urls) {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            const product = await scrapeProduct(page, url);
            if (product === false) {
                continue;
            }
            products.push(product);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://www.2bpet.co.kr/member/login.asp', { waitUntil: 'networkidle0' });
    await page.type('#id', username);
    await page.type('#pass', password);
    await page.click('#Frm > div > a');
    await page.waitForNavigation({ waitUntil: 'load', timeout: 600000 });
}
async function scrapeProduct(page, productHref) {
    try {
        const productImage = await getProductImage(page);
        if (productImage.includes('img_petzone_big.jpg')) {
            return false;
        }
        const productDetail = await getProductDetail(page);
        if (productDetail === false) {
            return false;
        }
        const productName = await getProductName(page);
        const hasOption = await getHasOption(page);
        const productOptions = hasOption ? await getProductOptions(page) : [];
        const productPrice = await page.evaluate(() => {
            const productPriceInput = document.querySelector('#packprice');
            if (productPriceInput) {
                const productPrice = productPriceInput.value;
                return parseInt(productPrice, 10);
            }
            return false;
        });
        if (productPrice === false) {
            return false;
        }
        if (productPrice < 1) {
            return false;
        }
        const product = {
            productName: productName,
            productPrice: productPrice,
            productImage: productImage,
            productDetail: productDetail,
            hasOption: hasOption,
            productOptions: productOptions,
            productHref: productHref,
            sellerID: 42
        };
        return product;
    } catch (error) {
        console.error('Error occurred:', error);
        return false;
    }
}

async function getProductDetail(page) {
    return await page.evaluate(() => {
        const productDetailElements = document.querySelectorAll('#tbContent > tbody > tr > td > div.contentZoom.contentSize');
        let imgSources = [];
        productDetailElements.forEach(element => {
            // 각 div.contentZoom.contentSize 요소 내의 모든 img 태그에 대해 순회
            const images = element.querySelectorAll('img');
            // 해당 img 태그들의 src 속성을 배열에 추가
            Array.from(images).forEach(img => imgSources.push(img.src));
        });
        // imgSources에 img의 src들이 저장됩니다.
        return imgSources.length > 0 ? imgSources : false;
    });
}

async function getProductImage(page) {
    const productImage = await page.evaluate(() => {
        return document.querySelector('#mainImg > li > span > img').src;
    });
    return productImage;
}
async function getProductOptions(page) {
    async function reloadSelects() {
        const selectHandles = await page.$$('select.select_fild');
        const filteredHandles = [];

        for (const handle of selectHandles) {
            const name = await page.evaluate(el => el.name, handle); // 각 요소의 name 속성 가져오기
            if (name !== 'deliveryCollectFl') { // 조건에 맞지 않는 이름 필터링
                filteredHandles.push(handle);
            }
        }

        return filteredHandles; // 필터링된 ElementHandle 배열 반환
    }

    async function resetSelects() {
        const delBtn = await page.$('#optList > div > span.btn_delete');
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
                    .filter(opt => opt.value !== '0' && opt.value !== '*' && opt.value !== '**' && !opt.text.toLowerCase().includes("품절")) // 품절 텍스트를 대소문자 구분 없이 필터링
            );

            for (const option of options) {
                await selects[currentDepth].select(option.value);
                await new Promise(resolve => setTimeout(resolve, 1000)); // AJAX 로딩 등 페이지 업데이트 대기
                const newSelectedOptions = [...selectedOptions, { text: option.text, value: option.value }];

                if (currentDepth + 1 < selects.length) {
                    const newSelects = await reloadSelects();
                    await processSelectOptions(newSelects, currentDepth + 1, newSelectedOptions, productOptions);
                } else {
                    let optionName = newSelectedOptions.map(opt =>
                        opt.text.replace(/\s*\([^)]*\)/g, "").trim() // 괄호와 괄호 안의 모든 문자 제거
                    ).join(", ");

                    const optionPrice = newSelectedOptions.reduce((total, opt) => {
                        // "원" 바로 앞의 숫자를 포함한 문자열을 추출합니다.
                        const matches = opt.text.match(/(\d[\d,\s]*)원/);
                        // 추출된 문자열에서 콤마와 공백을 제거한 후 숫자로 변환합니다.
                        return total + (matches ? parseInt(matches[1].replace(/[,\s]/g, ''), 10) : 0);
                    }, 0);

                    productOptions.push({ optionName, optionPrice });
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
async function getProductName(page) {
    const productName = await page.evaluate(() => {
        const selector = '#Frm > div > div.infoWrap > div > h1';
        const element = document.querySelector(selector);

        if (!element) return '';

        // HTML 태그와 "상품코드:", "class='subTit'"를 가진 span을 제거합니다.
        let innerHTML = element.innerHTML
            .replace(/상품코드:.*<\/font>/i, '')
            .replace(/<span class="subTit">.*?<\/span>/gi, '')
            .replace(/<[^>]*>/g, '');

        // HTML 엔티티 &nbsp;를 빈 문자열로 대체합니다.
        let productNameText = innerHTML.replace(/&nbsp;/gi, '');

        // 개행 문자와 탭을 빈 문자열로 대체합니다.
        productNameText = productNameText.replace(/[\n\t]/g, '');

        // 여러 개의 공백을 하나의 공백으로 대체합니다.
        productNameText = productNameText.replace(/\s+/g, ' ').trim();

        return productNameText;
    });

    return productName;
}



async function getHasOption(page) {
    return await page.evaluate(() => {
        const selectElements = document.querySelectorAll('select.select_fild');
        const filteredSelectElements = Array.from(selectElements).filter(select => select.name !== 'deliveryCollectFl');
        if (filteredSelectElements.length > 0) {
            return true;
        }
        return false;
    });
}



