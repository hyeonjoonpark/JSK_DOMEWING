const goToAttempts = async function (page, url, waitUntil, attempt = 0, maxAttempts = 3) {
    if (attempt >= maxAttempts) {
        return false;
    }
    let dialogAppeared = false;
    page.once('dialog', async dialog => {
        try {
            await dialog.accept();
        } catch (error) {
        } finally {
            dialogAppeared = true;
        }
    });
    try {
        await page.goto(url, { waitUntil });
        if (dialogAppeared) {
            return false;
        }
        return true;
    } catch (error) {
        return await goToAttempts(page, url, waitUntil, attempt++, maxAttempts);
    }
};
const signIn = async function (page, username, password, url, usernameSelector, passwordSelector, buttonSelector) {
    const goToAttemptsResult = await goToAttempts(page, url, 'networkidle0');
    if (goToAttemptsResult === false) {
        return false;
    }
    try {
        await page.evaluate((username, password, usernameSelector, passwordSelector, buttonSelector) => {
            document.querySelector(usernameSelector).value = username;
            document.querySelector(passwordSelector).value = password;
            document.querySelector(buttonSelector).click();
        }, username, password, usernameSelector, passwordSelector, buttonSelector);
    } catch (error) {
        return false;
    }
    try {
        await page.waitForNavigation({ waitUntil: 'load', timeout: 1000 });
    } catch (error) {
        return true;
    }
}
module.exports = {
    signIn,
    goToAttempts
};
