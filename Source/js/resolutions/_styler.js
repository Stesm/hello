const {debug} = require('./_variables');

class Styler {

    constructor() {}

    static style() {}

    static log(str) {
        if (debug)
           console.log(str);
    }

    static get selector() {
        return {}
    }
}

module.exports.Styler = Styler;
