const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const http = require('http');
(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        // const [tempFilePath] = args;
        // const imageUrls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const imageUrls = ['http://www.autocarfeel.co.kr/shop/data/goods/1303437589m0.jpg', 'http://www.autocarfeel.co.kr/shop/data/goods/1521424328814s0.jpg'];
        const relativePathToImagesFolder = '../../images/CDN/product';
        for (const imageUrl of imageUrls) {
            const parsedUrl = new URL(imageUrl);
            // URL의 경로 부분에서 파일명을 추출합니다.
            const filename = path.basename(parsedUrl.pathname);
            const savePath = path.join(__dirname, relativePathToImagesFolder, filename);
            await page.goto(imageUrl, { waitUntil: 'domcontentloaded' });
            await downloadImage(imageUrl, savePath);
        }
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();
async function downloadImage(url, savePath) {
    http.get(url, (res) => {
        const fileStream = fs.createWriteStream(savePath);
        res.pipe(fileStream);

        fileStream.on('finish', () => {
            fileStream.close();
        });
    }).on('error', (err) => {
        console.error('Failed to download the image:', err);
    });
}