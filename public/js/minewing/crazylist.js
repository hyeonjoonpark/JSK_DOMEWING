// node public/js/minewing/crazylist.js 'https://crazylist.co.kr/product/list.html?cate_no=26' 'jskorea2024' 'tjddlf88!@'
/**
 * id : jskorea2024
 * password : tjddlf88!@
 */

const puppeteer = require('puppeteer');
const { signIn, goToAttempts } = require('./common');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listUrl, id, password] = process.argv.slice(2);

    try {
        await signIn(
            page, id, password, 
            "https://crazylist.co.kr/member/login.html", 
            "#member_id", // 로그인 ID
            "#member_passwd", // 로그인 PW
            ".btnLogin" // 로그인 BUTTON
        );
        await goToAttempts(page, listUrl, 'domcontentloaded');
        const lastPageNumber = await getLastPageNumber(page);
        console.log(`lastPageNumber: ${lastPageNumber}`);

        const products = [];

        for (let i = lastPageNumber; i > 0; i--) {
            await goToAttempts(page, `${listUrl}&page=${i}`, 'domcontentloaded');
            const listProducts = await getListProducts(page);
            console.log(`page: ${i} / ${JSON.stringify(listProducts)}`);
            console.log(`${listProducts.length}개`);
            products.push(...listProducts);
        }

        console.log(`전체 갯수: ${products.length}`);
        console.log(products);
    } catch (err) {
        console.error(err);
    } finally {
        await browser.close();
    }
})();

async function getLastPageNumber(page) {
    const lastPageNumber = await page.evaluate(() => {
        const lastPageElement = document.querySelector('#contents > div.xans-element-.xans-product.xans-product-normalpaging.ec-base-paginate > a.last');
        if (!lastPageElement) return 1; // 수정: lastPageElement가 없을 경우 1을 반환

        const lastPageUrl = lastPageElement.getAttribute('href');
        const urlParams = new URLSearchParams(lastPageUrl.split('?')[1]);
        const pageValue = urlParams.get('page');
        return pageValue;
    });
    return lastPageNumber ? parseInt(lastPageNumber) : 1;
}

async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll(".box > span > span > div");
        const products = [];
        productElements.forEach(pe => {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        });

        function buildProduct(pe) {
            const isSoldOut = pe.querySelector('.prd_icon.left > span > img');
            if (isSoldOut) return false;

            const nameElement = pe.querySelector('.prd_name.left > a > span');
            if (!nameElement) return false;

            const priceElement = pe.querySelector('.xans-record-.prd_price > span'); // 수정: 선택자 오타 수정
            if (!priceElement) return false;

            const price = parseInt(priceElement.textContent.trim().replace(/[^\d]/g, ''));
            const imageElement = pe.querySelector('.prdImg_thumb > a > img');
            if (!imageElement) return false;

            const hrefElement = pe.querySelector('.prdImg_thumb > a'); // 수정: 올바른 링크를 가져오기 위해 선택자 수정
            if (!hrefElement) return false;
            
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            const href = `https://crazylist.co.kr${hrefElement.getAttribute('href')}`; // 수정: 올바른 링크를 가져오기 위해 수정
            const platform = 'CRAZYLIST';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
