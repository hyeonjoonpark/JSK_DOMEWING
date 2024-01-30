const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {

    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();