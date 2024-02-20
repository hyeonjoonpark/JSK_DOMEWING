const puppeteer = require('puppeteer');
async function login(page, username, password) {
    await page.goto('http://www.autocarfeel.co.kr/shop/member/login.php?&', { waitUntil: 'networkidle2', timeout: 0 });
    await page.type('#userid', username);
    await page.type('#password', password);
    await page.click('#doto_login > div.clearbox.mt20 > div.fleft > form > div > input.login-btn');
    await page.waitForNavigation();
}
