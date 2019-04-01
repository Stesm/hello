const $ = require('jquery');
const swiper = require('swiper');
const {Styler} = require('./_styler');

class mobileStyler extends Styler {
    style() {
        Styler.log('mobile styled');
    }
}

module.exports = new mobileStyler();
