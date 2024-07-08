
const fs = require('fs');
const puppeteer = require('puppeteer');
const { goToAttempts, signIn, scrollDown } = require('../common.js');
(async () => {
    const browser = await puppeteer.launch({ headless: false, args: ['--start-maximized'] });
    const page = await browser.newPage();
    await page.setViewport({ 'width': 1900, 'height': 1080 });
    let [tempFilePath, username, password] = process.argv.slice(2);
    await page.setDefaultNavigationTimeout(0);
    const urls = JSON.parse(fs.readFileSync(tempFilePath, 'utf8'));
    const products = [];
    let exitType = 0;
    try {
        await signIn(page, username, password, 'https://goodsdeco.com/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.member_login_box > div.login_input_sec > button');
        for (const url of urls) {
            const goToAttemptsResult = await goToAttempts(page, url, 'domcontentloaded');
            if (!goToAttemptsResult) {
                continue;
            }
            const product = await buildProduct(page, url);

            if (!product) {
                continue;
            }
            products.push(product);

        }
    } catch (error) {
        exitType = 1;
        errMsg = error;
    } finally {
        if (exitType === 0) {
            console.log(JSON.stringify(products));
        } else if (exitType === 1) {
            if (products.length > 0) {
                console.log(JSON.stringify(products));
            } else {
                console.error(errMsg);
            }
        }
        process.exit(exitType)
    }
})();
async function buildProduct(page, productHref) {
    // await scrollDown(page);
    // const isValidResult = await isValid(page);
    // if (!isValidResult) {
    //     return false;
    // }
    const productName = await getProductName(page);
    if (!productName) {
        return false;
    }
    const productPrice = await getproductPrice(page);
    if (!productPrice) {
        return false;
    }
    const productImage = await getproductImage(page);
    if (!productImage) {
        return false;
    }
    const productDetail = await getproductDetail(page);
    if (!productDetail) {
        return false;
    }
    const productOptions = await getproductOptions(page);
    if (productOptions === false) return false;
    const hasOption = productOptions.length > 0;

    return {
        productName,
        productPrice,
        productImage,
        productDetail,
        hasOption,
        productOptions,
        productHref,
        sellerID: 77
    };
}
// async function isValid(page) {
//     return await page.evaluate(() => {
//         const isSoldOut = Array.from(document.querySelectorAll('div.icon img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
//         if (isSoldOut) {
//             return false;
//         }
//         return true;
//     });
// }
async function getProductName(page) {
    return await page.evaluate(() => {
        const productNameElement = document.querySelector('#frmView > div > div > div.item_detail_tit > h3');
        if (!productNameElement) {
            return false;
        }
        return productNameElement.textContent.trim();
    });
}
async function getproductPrice(page) {
    return await page.evaluate(() => {
        const productPriceElement = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd');
        if (!productPriceElement) {
            return false;
        }
        return productPriceElement.textContent.replace(/[^0-9]/g, '').trim();
    });
}
async function getproductImage(page) {
    return await page.evaluate(() => {
        const productImageElement = document.querySelector('#mainImage > img');
        if (!productImageElement) {
            return false;
        }
        return productImageElement.src;
    });
}
async function getproductDetail(page) {
    return await page.evaluate(() => {

        let productDetail = [];

        const productDetailElementsImgCheckValid = document.querySelectorAll('#mainImage > img');
        const extraImg = document.querySelectorAll('#detail > div.detail_cont > div > div.txt-manual > div > img');
        const productDetailElements = document.querySelector('#contents > div > div.content_box > div.item_photo_info_sec > div + #frmView');
        if (!productDetailElements) {
            return false;
        }
        for (const imgs of productDetailElementsImgCheckValid) {
            const productDetailSrc = imgs.src;
            if (productDetailSrc)
                productDetail.push(productDetailSrc);
        }
        for (const imgs of extraImg) {
            const productDetailSrc = imgs.src;
            if (productDetailSrc)
                productDetail.push(productDetailSrc);

        }


        // let productRegularPrice = productDetailElements.querySelectorAll('div.item_detail_list > dl:nth-child(1) > dd')[0];
        // let productPrice = productDetailElements.querySelectorAll('div.item_detail_list > dl.item_price > dd')[0];
        // let productPurchaseRestriction = productDetailElements.querySelectorAll('div.item_detail_list > dl:nth-child(3) > dd')[0];
        // let productPurchaseBenefit = productDetailElements.querySelectorAll('div.item_detail_list > dl.item_discount_mileage > dd')[0];
        // let productDeliveryfees = productDetailElements.querySelectorAll('div.item_detail_list > dl.item_delivery > dd')[0];
        // let productCode = productDetailElements.querySelectorAll('div.item_detail_list > dl:nth-child(6) > dd')[0];
        // let productManufactureCompany = productDetailElements.querySelectorAll('div.item_detail_list > dl:nth-child(7) > dd')[0];
        // let productOrigin = productDetailElements.querySelectorAll('div.item_detail_list > dl:nth-child(8) > dd')[0];



        // if(productRegularPrice !== undefined) {
        //     productDetail.productRegularPrice = productRegularPrice.textContent.trim();
        // }

        // if(productPrice !== undefined) {
        //     productDetail.productPrice = productPrice.textContent.trim();
        // }
        // if(productPurchaseRestriction !== undefined) {
        //     productDetail.productPurchaseRestriction = productPurchaseRestriction.textContent.trim();
        // }
        // if(productPurchaseBenefit !== undefined) {
        //     productDetail.productPurchaseBenefit = productPurchaseBenefit.textContent.trim();
        // }
        // if(productDeliveryfees !== undefined) {
        //     productDetail.productDeliveryfees = productDeliveryfees.textContent.trim();
        // }
        // if(productCode !== undefined) {
        //     productDetail.productCode = productCode.textContent.trim();
        // }
        // if(productManufactureCompany !== undefined) {
        //     productDetail.productManufactureCompany = productManufactureCompany.textContent.trim();
        // }
        // if(productOrigin !== undefined) {
        //     productDetail.productOrigin = productOrigin.textContent.trim();
        // }

        if (productDetail.length < 1) {
            return false;
        }
        return productDetail;
    });
}
async function getproductOptions(page) {
    return await page.evaluate(() => {
        const productOptionElements = document.querySelectorAll('#frmView > div > div > div.item_detail_list > div > dl > dd > select option');
        const productOptionsObjec = Array.from(productOptionElements)
            .map(poe => poe)
            .filter(option => !option.getAttribute('disabled'));
        const productOptions = productOptionsObjec.map((val) => val.textContent.trim());
        if (productOptionElements.length > 0 && productOptions.length < 1) {
            return false;
        }
        return productOptions;
    });
}










// const fs = require('fs');
// const puppeteer = require('puppeteer');
// const { goToAttempts, signIn, scrollDown } = require('../common.js');
// (async () => {
//     const browser = await puppeteer.launch({ headless: false,args: ['--start-maximized'] });
//     const page = await browser.newPage();
//     const screenDimensions = await page.evaluate(() => {
//         return {
//             width: window.screen.availWidth,
//             height: window.screen.availHeight
//         };
//     });
//     await page.setViewport(screenDimensions);
//     const [tempFilePath, username, password] = process.argv.slice(2);
//     const fileContent = await fs.readFile(tempFilePath, 'utf8');
//     const urls = JSON.parse(fileContent);
//     try {
//         await signIn(page, username, password, 'https://goodsdeco.com/member/login.php', '#loginId', '#loginPwd', '#formLogin > div.member_login_box > div.login_input_sec > button');
//         const products = [];
//         for (const url of urls) {
//             const goToAttemptsResult = await goToAttempts(page, url, 'domcontentloaded');
//             if (!goToAttemptsResult) {
//                 continue;
//             }
//             const product = await buildProduct(page, ['https://www.goodsdeco.com/goods/goods_view.php?goodsNo=1000005474']);
//             if (!product) {
//                 continue;
//             }
//             products.push(product);
//         }
//         console.log(JSON.stringify(products));
//     } catch (error) {
//         console.error(error);
//     } finally {
//         await browser.close();
//     }
// })();
// async function buildProduct(page, productHref) {
//     await scrollDown(page);
//     // const isValidResult = await isValid(page);
//     // if (!isValidResult) {
//     //     return false;
//     // }
//     const productName = await getProductName(page);
//     if (!productName) {
//         return false;
//     }
//     const productPrice = await getproductPrice(page);
//     if (!productPrice) {
//         return false;
//     }
//     const productImage = await getproductImage(page);
//     if (!productImage) {
//         return false;
//     }
//     const productDetail = await getproductDetail(page);
//     if (!productDetail) {
//         return false;
//     }
//     const productOptions = await getproductOptions(page);
//     if (productOptions === false) return false;
//     const hasOption = productOptions.length > 0;
//     return {
//         productName,
//         productPrice,
//         productImage,
//         productDetail,
//         hasOption,
//         productOptions,
//         productHref,
//         sellerID: 76
//     };
// }
// async function isValid(page) {
//     return await page.evaluate(() => {
//         const isSoldOut = Array.from(document.querySelectorAll('div.icon img')).some(img => img.src === 'https://img.echosting.cafe24.com/design/skin/admin/ko_KR/ico_product_soldout.gif');
//         if (isSoldOut) {
//             return false;
//         }
//         return true;
//     });
// }
// async function getProductName(page) {
//     return await page.evaluate(() => {
//         const productNameElement = document.querySelector('#frmView > div > div > div.item_detail_tit > h3 > font > font');
//         if (!productNameElement) {
//             return false;
//         }
//         return productNameElement.textContent.trim();
//     });
// }
// async function getproductPrice(page) {
//     return await page.evaluate(() => {
//         const productPriceElement = document.querySelector('#frmView > div > div > div.item_detail_list > dl.item_price > dd');
//         if (!productPriceElement) {
//             return false;
//         }
//         return productPriceElement.textContent.replace(/[^0-9]/g, '').trim();
//     });
// }
// async function getproductImage(page) {
//     return await page.evaluate(() => {
//         const productImageElement = document.querySelector('#mainImage > img');
//         if (!productImageElement) {
//             return false;
//         }
//         return productImageElement.src;
//     });
// }
// async function getproductDetail(page) {
//     return await page.evaluate(() => {

//         if (!productDetailElements) {
//             return false;
//         }
//         const productDetail = [];


//         const productDetailElementsImgCheckValid = document.querySelectorAll('#mainImage > img');
//         const productDetailElements = document.querySelectorAll('#contents > div > div.content_box > div.item_photo_info_sec > div + #frmView')
//         for (const imgs of productDetailElementsImgCheckValid) {
//             const productDetailSrc = imgs.src;
//             if (productDetailSrc) {
//                 productDetail.push(productDetailSrc);
//                 productInfoContain = productDetailElements.querySelector('div > div');

//                 if(productInfoContain) {
//                     productRegularPrice = productDetailElements.querySelectorAll('div > div > div.item_detail_list > dl.item_price > dd')[0];
//                     productPurchaseRestriction = productDetailElements.querySelector('div > div > div.item_detail_list > dl > dd > font > font');

//                     if(productRegularPrice !== undefined) {
//                         productDetail.push(productRegularPrice.trim());
//                     }

//                     if(productPurchaseRestriction !== undefined) {
//                         productDetail.push(productPurchaseRestriction);
//                     }
//                 }
//             }
//         }
//         if (productDetail.length < 1) {
//             return false;
//         }



//         return productDetail;
//     });
// }
// async function getproductOptions(page) {
//     return await page.evaluate(() => {
//         const productOptionElements = document.querySelectorAll('optgroup option');
//         const productOptions = Array.from(productOptionElements)
//             .map(poe => poe.textContent.trim())
//             .filter(option => !option.includes('품절'));
//         if (productOptionElements.length > 0 && productOptions.length < 1) {
//             return false;
//         }
//         return productOptions;
//     });
// }
