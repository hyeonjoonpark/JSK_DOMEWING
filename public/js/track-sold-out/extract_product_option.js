const cheerio = require('cheerio');
module.exports = {
    getOptionName: function (productDetail) {
        const $ = cheerio.load(htmlString);
        const optionText = $('h1').text().trim();
        const optionContent = optionText.replace('옵션명 :', '').trim();
        return optionContent;
    }
};
