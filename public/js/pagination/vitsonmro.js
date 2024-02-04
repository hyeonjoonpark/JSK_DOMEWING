const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL] = args;
        await page.goto(listURL, { waitUntil: 'networkidle2', timeout: 0 });
        const numPages = await page.evaluate(() => {
            const numPagesText = document.querySelector('body > div.container > div > div.content > div.top_toolbar.align_side > div.tool_left > p > strong').textContent.trim();
            const numPages = parseInt(numPagesText.replace(/[^\d]/g, ''));
            return numPages;
        });
        console.log(numPages);
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();