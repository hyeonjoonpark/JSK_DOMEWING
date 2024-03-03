const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const http = require('http');
const https = require('https');

(async () => {
    const browser = await puppeteer.launch({ headless: true });
    const page = await browser.newPage();
    try {
        const args = process.argv.slice(2);
        const [tempFilePath] = args;
        const imageUrls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
        const relativePathToImagesFolder = '../../images/CDN/tmp';
        for (const { newFileName, imageSrc } of imageUrls) {
            const savePath = path.join(__dirname, relativePathToImagesFolder, newFileName);
            await downloadImage(imageSrc, savePath);
        }
        console.log(true);
    } catch (error) {
        console.error('Error occurred:', error);
    } finally {
        await browser.close();
    }
})();

async function downloadImage(url, savePath) {
    // URL의 프로토콜에 따라 적절한 모듈을 선택
    const client = url.startsWith('https') ? https : http;

    client.get(url, (res) => {
        if (res.statusCode === 200) {
            const fileStream = fs.createWriteStream(savePath);
            res.pipe(fileStream);

            fileStream.on('finish', () => {
                fileStream.close();
                console.log('Download finished:', savePath);
            });
        } else {
            console.error('Failed to download the image. Status code:', res.statusCode);
        }
    }).on('error', (err) => {
        console.error('Failed to download the image:', err);
    });
}