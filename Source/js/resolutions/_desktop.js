const {Styler} = require('./_styler');

class desktopStyler extends Styler {
    style() {
        Styler.style();
    }
}

module.exports = new desktopStyler();
