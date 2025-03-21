const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [listURL, username, password] = args;
        await signIn(page, username, password);
        const numPage = await getNumPage(page, listURL);
        const products = [];
        for (let i = numPage; i > 0; i--) {
            await moveToPage(page, i);
            let list = await scrapeProducts(page);
            products.push(...list);
        }
        console.log(JSON.stringify(products));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();
async function getNumPage(page, url) {
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 0 });
    await page.evaluate(() => {
        // iframe 요소 숨기기를 위한 함수 정의
        const hideIframeBySelector = (selector) => {
            const iframe = document.querySelector(selector);
            if (iframe) {
                iframe.style.display = 'none';
            }
        };

        // 특정 iframe 요소들을 숨김
        hideIframeBySelector('body > iframe.sc-fKFyDc.nwOmR.enter.done');
        hideIframeBySelector('body > iframe.sc-iBaPrD.gdARiY.sc-eggNIi.JiLrb.enter.done');

        // 첫 번째 버튼을 클릭
        const firstButton = document.querySelector('body > div.container > div > div.content > div.area_wrap > div.ken_td_wrap > div.td_bar > div.bar_right > div button');
        if (firstButton) {
            firstButton.click();
        }
    });
    await new Promise((page) => setTimeout(page, 3000));
    await page.select('#grid > div.k-pager-wrap.k-grid-pager.k-widget.k-floatwrap > span.k-pager-sizes.k-label > span > select', '60');
    await new Promise((page) => setTimeout(page, 3000));
    const numProducts = await page.evaluate(() => {
        const numProductsText = document.querySelector('body > div.container > div > div.content > div.top_toolbar.align_side > div.tool_left > p > strong').textContent.trim();
        const numProducts = parseInt(numProductsText.replace(/[^\d]/g, ''));
        return numProducts;
    });
    const numPerPage = 60;
    const numPage = Math.ceil(numProducts / numPerPage);
    return numPage;
}
async function goToWithRepeat(page, url, index, wiatUntilType) {
    try {
        await page.goto(url, { waitUntil: wiatUntilType });
        return true;
    } catch (error) {
        if (index < 3) {
            index++
            await goToWithRepeat(page, url, index, wiatUntilType);
        } else {
            return false;
        }
    }
}
async function signIn(page, username, password) {
    await goToWithRepeat(page, 'https://vitsonmro.com/mro/login.do', 0, 'networkidle0');
    await new Promise((page) => setTimeout(page, 3000));
    // await page.evaluate(() => {
    //     const isPopup = document.querySelector('#groobeeWrap');
    //     if (isPopup) {
    //         isPopup.style.display = 'none';
    //         document.querySelector('body > div.grbDim.grbLayer').style.display = 'none';
    //     }
    // });
    await page.type('#custId', username);
    await page.type('#custPw', password);
    await page.click('#loginForm > div > a:nth-child(3)');
    await page.waitForSelector('#wrap');
}
async function moveToPage(page, curPage) {
    curPage = parseInt(curPage);
    const selector = `a[data-page="${curPage}"]`; // 동적 셀렉터 생성
    const result = await page.evaluate(selector => {
        const link = document.querySelector(selector);
        if (link) {
            link.click(); // 링크가 존재하면 클릭
            return true;
        } else {
            return false;
        }
    }, selector);
    await new Promise((page) => setTimeout(page, 3000));
    return result;
}
async function scrapeProducts(page) {
    const products = await page.evaluate(() => {
        function processProduct(productElement) {
            const eachAmountElement = productElement.querySelector('td:nth-child(13) > span.hdsp_bot.price_tt');
            if (!eachAmountElement) {
                return false;
            }
            const eachAmount = parseInt(eachAmountElement.textContent.replace(/[^0-9]/g, '').trim(), 10);
            if (eachAmount > 1) {
                return false;
            }
            const stockText = productElement.querySelector('td:nth-child(9) > span.hdsp_bot').textContent.trim();
            if (stockText !== '재고보유') {
                return false;
            }
            const tagElements = productElement.querySelectorAll('td:nth-child(5) > span.hdsp_bot > div span');
            const sameDayShipping = '당일출고';
            const forbidden = '인터넷판매불가';
            const hasSameDayShipping = Array.from(tagElements).some(tagElement => tagElement.textContent.trim() === sameDayShipping);
            const hasForbidden = Array.from(tagElements).some(tagElement => tagElement.textContent.trim() === forbidden);
            if (hasForbidden) {
                return false;
            }
            if (!hasSameDayShipping) {
                return false;
            }
            const productName = productElement.querySelector('td:nth-child(6) > span.hdsp_top.link > a').textContent.trim();
            const standard = productElement.querySelector('td:nth-child(6) > span.hdsp_bot').textContent.trim();
            const name = productName + ' ' + standard;
            const productPriceText = productElement.querySelector('td:nth-child(10) > span.hdsp_top.price_cr').textContent;
            const price = productPriceText.replace(/[^0-9]/g, '').trim();
            const image = productElement.querySelector('td:nth-child(4) > div > img').getAttribute('src');
            if (image.includes('이미지준비중')) {
                return false;
            }
            const productCode = productElement.querySelector('td:nth-child(5) > span.hdsp_top').textContent.replace(/[^0-9]/g, '').trim();
            const href = 'https://vitsonmro.com/mro/shop/productDetail.do?productCode=' + productCode;
            const platform = '비츠온엠알오';
            return { name, price, image, href, platform };
        }
        const productElements = document.querySelectorAll('#grid > div.k-grid-content.k-auto-scrollable > table > tbody tr');
        const products = [];
        for (const productElement of productElements) {
            const result = processProduct(productElement);
            if (result !== false) {
                products.push(result);
            }
        }
        return products;
    });
    return products;
}
