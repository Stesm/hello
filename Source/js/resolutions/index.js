const $ = require('jquery');
const {Styler} = require('./_styler');
const manager = {
    mobile : require('./_mobile'),
    tablet : require('./_tablet'),
    desktop : require('./_desktop'),
};

function Manager(){
    const {
        tablet,
        desktop
    } = require('./_variables');

    let o = this;
    let resolution = 'desktop';

    this.manage = () => {
        if (o.setResolution())
            o.styleCurrent();
    };

    this.styleCurrent = () => {
        manager[resolution].style();
    };

    this.setResolution = () => {
        const width = $(window).width();

        if (width < tablet && resolution !== 'mobile') {
            resolution = 'mobile';
            Styler.log('resolution changed to: mobile');
            o.reset();

            return true;
        } else if (width >= tablet && width < desktop && resolution !== 'tablet') {
            resolution = 'tablet';
            Styler.log('resolution changed to: tablet');
            o.reset();

            return true;
        } else if (width >= desktop && resolution !== 'desktop') {
            resolution = 'desktop';
            Styler.log('resolution changed to: desktop');
            o.reset();

            return true;
        }

        return false;
    };

    this.reset = () => {
        Styler.log('all params cleaned');
    };
}

module.exports = new Manager();
