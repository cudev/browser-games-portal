const Dashboard = require('./components/Dashboard.js'),
    Vue = require('vue'),
    WebFont = require('webfontloader');
require('../stylesheets/main.scss')

// Load fonts
WebFont.load({
    google: {
        families: ['Roboto:400,100,300,100italic,300italic,500,400italic,500italic,700,700italic:latin']
    }
});

Vue.config.delimiters = ['${', '}'];
Vue.config.debug = true;

const dashboard = new Dashboard({el: 'body'});