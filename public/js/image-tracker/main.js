const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const http = require('http');
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
            // Ensure the correct URL is used for navigation and downloading
            // await page.goto(imageSrc, { waitUntil: 'domcontentloaded' });
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