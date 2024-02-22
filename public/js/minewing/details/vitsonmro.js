const puppeteer = require('puppeteer');
const fs = require('fs');

// 메인 로직을 처리하는 비동기 함수
async function main() {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const [tempFilePath, username, password] = process.argv.slice(2);
        const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));

        if (!await signIn(page, username, password)) {
            console.error('Sign-in failed');
            return;
        }

        const products = await scrapeProducts(page, urls);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
}

// 상품 정보를 수집하는 함수
async function scrapeProducts(page, urls) {
    const products = [];
    for (const url of urls) {
        if (await navigateWithRetry(page, url)) {
            const product = await scrapeProduct(page, url);
            products.push(product);
        } else {
            console.error(`Failed to navigate to ${url}`);
        }
    }
    return products;
}

// 페이지 이동을 재시도하는 함수
async function navigateWithRetry(page, url, attempts = 3, delay = 2000) {
    for (let i = 0; i < attempts; i++) {
        try {
            await page.goto(url, { waitUntil: 'networkidle0' });
            return true; // 성공적으로 페이지를 로드했음을 나타냄
        } catch (error) {
            console.error(`Attempt ${i + 1} to navigate to ${url} failed: ${error}`);
            if (i < attempts - 1) {
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    return false; // 모든 시도가 실패했음을 나타냄
}

// 로그인을 수행하는 함수
async function signIn(page, username, password) {
    if (!await navigateWithRetry(page, 'https://vitsonmro.com/mro/login.do')) {
        return false;
    }

    await page.type('#custId', username);
    await page.type('#custPw', password);
    await page.click('#loginForm > div > a:nth-child(3)');
    await page.waitForSelector('#wrap');
    return true;
}

// 상품 페이지에서 정보를 수집하는 함수
async function scrapeProduct(page, productHref) {
    return page.evaluate((href) => {
        const productName = document.querySelector('selector').textContent.trim();
        const productPrice = document.querySelector('selector').textContent.trim();
        // 추가적인 상품 정보 수집 로직
        return { productName, productPrice, /* 기타 필드 */ };
    }, productHref);
}

main();