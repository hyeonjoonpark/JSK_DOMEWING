const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i >= 0; i--) {
            await moveToPage(page, i);
            let list = await scrapeProducts(page);
            if (list.length > 0) {
                products.push(...list);
            }
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function signIn(page, username, password) {
    await page.goto('https://www.2bpet.co.kr/member/login.asp', { waitUntil: 'networkidle0' });
    await page.type('#id', username);
    await page.type('#pass', password);
    await page.click('#Frm > div > a');
    await page.waitForNavigation({ waitUntil: 'load' });
}
async function getNumPage(page, listUrl) {
    await page.goto(listUrl, { waitUntil: 'networkidle0' });
    await page.select('#viewCount', '20');
    await new Promise(resolve => setTimeout(resolve, 10000));
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('#container > div > div > div.view_type_box > p > em').innerHTML.trim();
        const matches = numProductsText.match(/\d+/g);
        const numProducts = parseInt(matches[0], 10);
        return numProducts;
    });
    const countProductInPage = 100;
    const numPage = Math.floor(numProducts / countProductInPage);
    return numPage;
}
async function moveToPage(page, curPage) {
    const sibalPage = curPage * 100;
    await page.evaluate((sibalPage) => {
        paging('pStart', '' + sibalPage);
    }, sibalPage);
    await new Promise(resolve => setTimeout(resolve, 20000));
}
async function scrapeProducts(page) {
    return await page.evaluate(() => {
        const querySelectorTextContent = (element, selector) => {
            const foundElement = element.querySelector(selector);
            return foundElement ? foundElement.textContent.trim() : '';
        };

        const querySelectorSrc = (element, selector) => {
            const foundElement = element.querySelector(selector);
            return foundElement ? foundElement.src : '';
        };

        const isProductValid = (productElement) => {
            const skipImageSrc = [
                "https://www.2bpet.co.kr/data/petzone/icon/05.jpg",
                "https://www.2bpet.co.kr/data/petzone/icon/06.jpg",
                "https://www.2bpet.co.kr/data/petzone/icon/03.jpg",
                "https://www.2bpet.co.kr/data/petzone/icon/04.jpg",
                "https://www.2bpet.co.kr/data/petzone/icon/01.jpg"
            ];
            const skipKeywords = ["온라인", "유통기한", "판매금지"];
            const skipPriceText = "품절";

            const imageElementSrc = querySelectorSrc(productElement, 'div.isiconBoxTop > div > span > img');
            if (skipImageSrc.includes(imageElementSrc)) {
                return false;
            }

            const productName = querySelectorTextContent(productElement, 'div.txt > div.tit > a');
            if (!productName || skipKeywords.some(keyword => productName.includes(keyword))) {
                return false;
            }

            const productPrice = querySelectorTextContent(productElement, 'div.txt > div.price_wrap > div > span.price');
            if (!productPrice || productPrice.includes(skipPriceText)) {
                return false;
            }

            return true;
        };

        return Array.from(document.querySelectorAll('div.con'))
            .filter(isProductValid)
            .map(productElement => ({
                name: querySelectorTextContent(productElement, 'div.txt > div.tit > a'),
                price: querySelectorTextContent(productElement, 'div.txt > div.price_wrap > div > span.price').replace(/[^\d]/g, ''),
                image: querySelectorSrc(productElement, 'div.list_top > a > img'),
                href: productElement.querySelector('div.list_top > a').href,
                platform: '투비펫'
            }));
    });
}
