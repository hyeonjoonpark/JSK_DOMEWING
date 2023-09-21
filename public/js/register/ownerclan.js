const puppeteer = require('puppeteer');
const mysql = require('mysql2/promise');
const path = require('path');

(async () => {
    const browser = await puppeteer.launch({ headless: false });
    const page = await browser.newPage();

    try {
        const args = process.argv.slice(2);
        const [itemName, invoiceName, category, keywords, taxability, imageName, saleToMinor, origin, madicalEquipment, healthFunctional, shipping, price, vendor, shipCost, product_information, model, productDescImage, username, password] = args;

        await page.goto('https://ownerclan.com/vender/');
        const frame = await page.frames().find(frame => frame.name() === 'vmainframe');
        await frame.type('input[name="id"]', username);
        await frame.type('input[name="passwd"]', password);
        await frame.click('input[type="submit"]');
        await frame.waitForNavigation();
        await page.goto('https://ownerclan.com/vender/product_register.php');

        await page.click('#form1 > table > tbody > tr > td > table > tbody > tr:nth-child(5) > td > table > tbody > tr:nth-child(5) > td > label:nth-child(2) > input[type=radio]');
        await page.type('#category_search_text', category);
        await page.waitForSelector('.ui-autocomplete li:first-child a');
        await page.click('.ui-autocomplete li:first-child a');

        await page.type('input[name="productname"]', itemName);
        await page.type('input[name="productname_deli"]', itemName);
        await page.type('input[name="ompkeyword"]', keywords);
        await page.click('#origin_nation3');
        await page.type('#production', vendor);
        await page.type('input[name="model"]', model);
        await page.type('#buyprice', price);

        const inputValue = (taxability === "과세") ? "Y" : "N";
        await page.click(`input[name="tax_mode"][value="${inputValue}"]`);

        const filePath = path.join(__dirname, 'public', 'assets', 'images', 'product', imageName);
        const fileInput = await page.$('input[type="file"][name="userfile"]');
        await fileInput.uploadFile(filePath);

        const saleToMinorValue = (saleToMinor === "가능") ? "Y" : "N";
        await page.click(`input[name="sell_minors"][value="${saleToMinorValue}"]`);

        if ((healthFunctional === "건강기능식품 아님" && madicalEquipment === "의료기기") || (healthFunctional === "건강기능식품" && madicalEquipment === "의료기기 아님")) {
            await page.click('input[name="medicalAttr"][value="' + ((healthFunctional === "건강기능식품 아님") ? "Y" : "N") + '"]');
            await page.click('input[name="hfoodAttr"][value="' + ((madicalEquipment === "의료기기 아님") ? "Y" : "N") + '"]');
        }

        const iframeSelector = 'iframe[src="./se282/SmartEditor2Skin.html"]';
        await page.waitForSelector(iframeSelector);
        const frameHandle = await page.$(iframeSelector);
        const iframe = await frameHandle.contentFrame();
        await iframe.click('button[class="se2_to_html"]');
        await iframe.evaluate((productDesc) => {
            document.querySelector('textarea[class="se2_input_syntax se2_input_htmlsrc"]').value = '<p style="text-align:center;"><img src="https://i.imgur.com/KaMAbtW.png" /></p>';
        }, productDescImage);

        const shippingValue = (shipping === "선불") ? "S" : (shipping === "착불") ? "G" : "F";
        await page.click(`input[name="deli"][value="${shippingValue}"]`);

        if (shipping === "선불") {
            await page.type('input[name="buydeliprice"]', shipCost);
        }

        await page.select('select[name="nfcategory"]', "35");
        await wait(2);
        await page.click('input[id="all_nf"]');
        await page.click('#submitTR > td > a');
        await page.click('#smtChk');
        page.on('dialog', async dialog => {
            console.log('here');
            await dialog.accept();
        });
        console.log(JSON.stringify(resultMsg));
    } catch (error) {
        console.error(error);
    } finally {
        await browser.close();
    }
})();

async function getProductDescValue(product_information) {
    try {
        const connection = await createDBConnection();
        const [rows] = await connection.execute('SELECT `ownerclan_value` FROM product_information WHERE domesin_value = ?', [product_information]);
        if (rows.length > 0) {
            const productDescValue = rows[0].ownerclan_value;
            connection.end();
            return productDescValue;
        } else {
            throw new Error('Product not found');
        }
    } catch (error) {
        console.error('Error:', error.message);
        throw new Error('Error fetching product description');
    }
}

async function getProductDesc(productId) {
    try {
        const connection = await createDBConnection();
        const [rows] = await connection.execute('SELECT `desc` FROM product WHERE id = ?', [productId]);
        if (rows.length > 0) {
            const productDesc = rows[0].desc;
            connection.end();
            return productDesc;
        } else {
            throw new Error('Product not found');
        }
    } catch (error) {
        console.error('Error:', error.message);
        throw new Error('Error fetching product description');
    }
}

async function getAccount(userId, vendorId) {
    try {
        const connection = await createDBConnection();
        const [rows] = await connection.execute('SELECT accounts.username, accounts.password FROM accounts JOIN IN users ON users.id=accounts.user_id JOIN IN vendors ON vendors.id=accounts.vendor_id WHERE accounts.user_id=?', [userId]);
        if (rows.length > 0) {
            const account = rows[0];
            connection.end();
            return account;
        } else {
            throw new Error('Product not found');
        }
    } catch (error) {
        console.log(error);
    }
}

async function createDBConnection() {
    return await mysql.createConnection({
        host: 'localhost',
        user: 'parkchansu39',
        password: 'Qkrckstn1!',
        database: 'domewing'
    });
}

async function wait(seconds) {
    return new Promise(resolve => setTimeout(resolve, seconds * 1000));
}
