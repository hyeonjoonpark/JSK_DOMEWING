// b2bgobycar Details
// node public/js/minewing/details/b2bgobycar.js "jskorea2024" "tjddlf88!@"
/**
 * id : jskorea2024
 * password : tjddlf88!@
 */

const puppeteer = require("puppeteer");
const { signIn, goToAttempts } = require("./common");

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    const [listUrl, id, password] = process.argv.slice(2);

    try {
        await signIn(
            page, id, password,
            "http://b2bgobycar.co.kr/member/login.php",
            "#loginId",
            "#loginPwd",
            ".skinbtn.point2.l-login"
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
        const lastPageUrl = document.querySelector('a[aria-label="Last"]').getAttribute('href');
        const match = lastPageUrl.match(/page=(\d+)/);
        return match ? parseInt(match[1]) : 1;
    });
    return lastPageNumber;
}

async function getListProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll("#content > div > div > div > div.goods-list > div > div > ul > li");
        const products = [];
        productElements.forEach(pe => {
            const product = buildProduct(pe);
            if (product) {
                products.push(product);
            }
        });

        function buildProduct(pe) {
            const nameElement = pe.querySelector('div > div.txt > a > strong');
            if (!nameElement) return false;

            const priceElement = pe.querySelector('.price.gd-default > span');
            if (!priceElement) return false;

            const price = parseInt(priceElement.textContent.trim().replace(/[^\d]/g, ''));
            const imageElement = pe.querySelector('.thumbnail > a > img');
            if (!imageElement) return false;

            const hrefElement = pe.querySelector('.txt > a');
            if (!hrefElement) return false;
            
            const name = nameElement.textContent.trim();
            const image = imageElement.src;
            const href = `https://b2bgobycar.co.kr/${hrefElement.getAttribute('href')}`;
            const platform = 'B2B고바이카';
            return { name, price, image, href, platform };
        }
        return products;
    });
    return products;
}
