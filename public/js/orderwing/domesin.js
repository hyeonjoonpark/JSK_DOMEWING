const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://www.domesin.com/scm/login.html', { waitUntil: 'networkidle2' });
        const usernameInput = await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(1) > td > input');
        const passwordInput = await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(1) > table > tbody > tr:nth-child(3) > td > input');
        const signInBtn = await page.waitForSelector('body > table > tbody > tr:nth-child(1) > td > table > tbody > tr > td > table > tbody > tr > td:nth-child(3) > input[type=image]');
        await usernameInput.type(username);
        await passwordInput.type(password);
        await signInBtn.click();
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('http://domesin.com/scm/M_order/list.html', { waitUntil: 'networkidle2' });
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
