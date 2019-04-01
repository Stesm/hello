const $ = require('jquery');
const swiper = require('swiper');
const {Styler} = require('./_styler');

class tabletStyler extends Styler {
    style() {
        Styler.log('tablet styled');
    }
}

module.exports = new tabletStyler();
