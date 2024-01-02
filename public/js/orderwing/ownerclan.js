const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [username, password] = args;
        await page.goto('https://ownerclan.com/vender/', { waitUntil: 'networkidle2' });
        const frame = page.frames().find(f => f.name() === 'vmainframe');
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(1) > input', username);
        await frame.type('body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(2) > input', password);
        await frame.evaluate(() => {
            document.querySelector("body > table:nth-child(1) > tbody > tr:nth-child(2) > td > div:nth-child(2) > div:nth-child(1) > p:nth-child(3) > input[type=submit]").click();
        });
        await frame.waitForNavigation({ waitUntil: 'networkidle2' });
        await page.goto('https://ownerclan.com/vender/order_list.php', { waitUntil: 'networkidle2' });
        page.on('dialog', async dialog => {
            await dialog.dismiss();
            return;
        });
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
