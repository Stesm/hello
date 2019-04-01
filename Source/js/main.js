const $ = require('jquery');
const manager = require('./resolutions');
const form = require('./request-form');

form.bind();
manager.styleCurrent();

$(window).resize(manager.manage);
