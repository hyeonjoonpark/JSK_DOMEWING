const cheerio = require('cheerio');
module.exports = {
    getOptionName: function (productDetail) {
        const $ = cheerio.load(productDetail);
        const optionText = $('h1').text().trim();
        if (!optionText) {
            return '';
        }
        const optionContent = optionText.replace('옵션명 :', '').trim();
        return optionContent;
    }
};
