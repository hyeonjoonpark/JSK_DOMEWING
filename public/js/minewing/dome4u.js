const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    const [listURL, username, password] = process.argv.slice(2);

    try {
        await login(page, username, password);
        await processPage(page, listURL);
        const products = await scrapeProducts(page);
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function login(page, username, password) {
    await page.goto('https://dome4u.co.kr/home/member/login.php', { waitUntil: 'networkidle0' });
    await page.type('#id', username);
    await page.type('#passwd', password);
    await page.click('#login_submit');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
}

async function processPage(page, listURL) {
    await page.goto(listURL);
    await page.waitForSelector('#contents > div.conInfo.clearfix > em');
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#contents > div.conInfo.clearfix > em').textContent;
        return parseInt(numProductsText.replace(/[^0-9]/g, '').trim());
    });
    const url = new URL(listURL);
    const params = new URLSearchParams(url.search);
    const cate = params.get('cate');
    const newUrl = "https://dome4u.co.kr/home/product/?brand=&sortby=viewcount&ctl=m&seller_id=&prttp=&brandname=&brandsearchcate1=&brandsearchcate2=&glimit=&dp_view_type=&brandcategory=&cate=" + cate + "&search_f=&search_word=&cou=&pon=&delv1=&delv2=&delv3=&delv4=&stprice=&edprice=&rsearch_word=&view_type=0&page=1&sortby=viewcount&glimit=&view_type=1&glimit=" + numProducts;
    await page.goto(newUrl, { waitUntil: 'domcontentloaded', timeout: 300000 });
}

async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        const productElements = document.querySelectorAll('#tab111 > div dl');
        const products = [];

        function processProduct(productElement) {
            const productNameElement = productElement.querySelector('#tab111 > div> dl > dd.pName > a');
            const name = productNameElement.textContent.trim();
            const productPriceText = productElement.querySelector('#tab111 > div > dl > dd.price.type2').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const imageElement = productElement.querySelector('#tab111 > div > dl > dd.imgArea > a > img');
            const image = imageElement.src;
            const href = productElement.querySelector('#tab111 > div > dl > dd.imgArea > a').href.trim();
            const platform = '도매포유';

            return { name, price, image, href, platform };
        }
        function hasStockByText(productElement) {
            // 상품 요소 내의 모든 텍스트 노드를 검사하여 '품절' 텍스트가 있는지 확인
            const textContent = productElement.textContent || productElement.innerText;
            return !textContent.includes('품절');
        }

        for (const productElement of productElements) {
            if (hasStockByText(productElement)) {
                try {
                    const productInfo = processProduct(productElement);
                    products.push(productInfo);
                } catch (error) {
                    console.error("Error processing product: ", error);
                }
            }
        }

        return products;
    });

    return products;
}



//         function hasStockMethod(productElement) {
//             const soldOutImage = productElement.querySelector('li > div > div.txt > div > img');
//             return !soldOutImage;
//         }

//         for (const productElement of productElements) {
//             const hasStockMethodResult = hasStockMethod(productElement);
//             if (hasStockMethodResult === false) {
//                 continue;
//             }
//             try {
//                 const productInfo = processProduct(productElement);
//                 products.push(productInfo);
//             } catch (error) {
//                 console.error("Error processing product: ", error);
//                 continue;
//             }
//         }

//         return products;
//     });

//     return products;
// }

