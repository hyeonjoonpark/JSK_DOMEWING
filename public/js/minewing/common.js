const axios = require('axios');

const checkProductName = async (productName) => {
    const forbiddenword = [
        '온라인',
        '금지',
        '최소',
        '준수',
        '특가',
        '불가',
        '삭제',
        '대량',
        '박스',
        '해외',
        '직구',
        '대행',
        '업체',
        '오프라인',
        '매장',
        '주문',
        '재고',
        '할인',
        '세일',
        '본사',
        '공급'
    ].filter(forbiddenword => productName.includes(forbiddenword));
    return forbiddenword.length > 0 ? false : true;
};
const formatProductName = async (productName) => {
    const formattedProductName = productName.replace(/_[A-Z0-9]+/, '');

    if (!formattedProductName) {
        return false;
    }

    return formattedProductName;
}
const trimProductCodes = async (productName) => {
    //sample could be "Castelbajac Women's Golf (Short) 4-Pair_CSW-127"
    const splitProductName = productName.split(' ');
    const productCodesGetString = splitProductName[splitProductName.length - 1];

    const productCodeRegex = /([A-Z0-9]+-[\d]+)$/;
    let match = productCodesGetString.match(productCodeRegex);
    if (match) {
        splitProductName.pop();
        return splitProductName.join(' ') + ' ' + productCodesGetString.replace(match[0], '').replace('_', '');
        // Castelbajac Women's Golf (Short) 4-Pair
    }
    return productName; // if no match of product codes then return original.
}


const checkImageUrl = async (url) => {
    try {
        const response = await axios.head(url);
        return response.status !== 404;
    } catch (error) {
        return false;
    }
};
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

module.exports = { goToAttempts, scrollDown, signIn, checkImageUrl, checkProductName, formatProductName, trimProductCodes };

