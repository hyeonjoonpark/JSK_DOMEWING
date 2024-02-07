const puppeteer = require('puppeteer');
const getProductDetails = async (page, productHref) => {
    await page.goto(productHref, { waitUntil: 'load' });
    return page.evaluate(() => {
        const baseURL = 'https://dometopia.com';
        const productImages = document.querySelectorAll('#goods_thumbs > div.box > div.slides_container.hide img');
        const productImage = productImages.length > 3 ? productImages[2].src : productImages[0]?.src;

        const images = document.querySelectorAll('#detail > div > div.section.info > div.goods_description > div.detail-img img');
        const productDetail = images.length === 0 ? [] : Array.from(images, img => {
            let src = img.getAttribute('src');
            return src.startsWith('http://') || src.startsWith('https://') ? src : new URL(src, baseURL).href;
        });

        return {
            productImage,
            productDetail,
        };
    });
};
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const [productHref] = process.argv.slice(2);
        const productContents = await getProductDetails(page, productHref);
        console.log(JSON.stringify(productContents));
    } catch (error) {
        console.log(false);
    } finally {
        await browser.close();
    }
})();
