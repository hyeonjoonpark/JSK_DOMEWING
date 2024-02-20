const puppeteer = require('puppeteer');

const getProductDetails = async (page) => {
    const productImage = await page.evaluate(() => {
        const productImages = Array.from(document.querySelectorAll('#goods_thumbs > div.box > div.slides_container.hide img'));
        return productImages.length > 3 ? productImages[2].src : productImages[0]?.src;
    });

    const productDetail = await page.evaluate(() => {
        const baseURL = 'https://dometopia.com';
        const images = Array.from(document.querySelectorAll('#detail > div > div.section.info > div.goods_description > div.detail-img img'));
        return images.map(img => {
            let src = img.getAttribute('src');
            return src.startsWith('http://') || src.startsWith('https://') ? src : new URL(src, baseURL).href;
        });
    });

    return { productImage, productDetail };
};

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const [productHref] = process.argv.slice(2);
        try {
            await page.goto(productHref, { waitUntil: 'domcontentloaded' });
        } catch (error) {
            console.log(false);
        }
        const productContents = await getProductDetails(page);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.log(false);
    } finally {
        await browser.close();
    }
})();
