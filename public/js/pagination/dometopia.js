const puppeteer = require('puppeteer');
async function getFinalPageNum(page, listURL, index) {
    const fullURL = `${listURL}&perpage=150&page=${index}`;
    await page.goto(fullURL, { waitUntil: 'networkidle2', timeout: 0 });

    const isLastPage = await page.evaluate(() => {
        return !document.querySelector('#product_list > nav > div > a.last');
    });

    if (!isLastPage) {
        return getFinalPageNum(page, listURL, index + 5);
    }

    return await page.evaluate(() => {
        const lastPageLink = [...document.querySelectorAll('#product_list > nav > div a')].pop();
        const finalPageNum = parseInt(lastPageLink.textContent);
        return finalPageNum;
    });
}
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [listURL] = args;
        const index = 1;
        const finalPageNum = await getFinalPageNum(page, listURL, index);
        console.log(finalPageNum * 150);
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();