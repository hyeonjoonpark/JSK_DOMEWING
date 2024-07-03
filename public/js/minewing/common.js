const goToAttempts = async (page, url, waitUntil, attempt = 0, maxAttempts = 3) => {
    if (attempt >= maxAttempts) {
        return false;
    }
    try {
        await page.goto(url, { waitUntil });
        return true;
    } catch (error) {
        return await goToAttempts(page, url, waitUntil, attempt + 1, maxAttempts); // increment the attempt count correctly
    }
};
const signIn = async (page, username, password, url, usernameSelector, passwordSelector, buttonSelector) => {
    const goToAttemptsResult = await goToAttempts(page, url, 'networkidle0');
    if (!goToAttemptsResult) {
        return false;
    }
    try {
        await page.type(usernameSelector, username);
        await page.type(passwordSelector, password);
        await page.click(buttonSelector);

        await page.waitForNavigation({ waitUntil: 'load', timeout: 30000 }); // increased timeout to ensure the navigation completes
        return true;
    } catch (error) {
        console.error('Sign-in failed:', error);
        return false;
    }
};
const scrollDown = async (page) => {
    await page.evaluate(async () => {
        const distance = 45;
        const scrollInterval = 50;
        while (true) {
            const scrollTop = window.scrollY;
            const prdDetailElement = document.getElementById('prdDetail');
            const prdInfoElement = document.getElementById('prdInfo');
            if (prdDetailElement) {
                const targetScrollBottom = prdDetailElement.getBoundingClientRect().bottom + window.scrollY;
                if (scrollTop < targetScrollBottom) {
                    window.scrollBy(0, distance);
                } else {
                    break;
                }
            } else if (prdInfoElement) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                break;
            } else {
                window.scrollBy(0, distance);
            }

            await new Promise(resolve => setTimeout(resolve, scrollInterval));
        }
    });
}
module.exports = { goToAttempts, scrollDown, signIn };
