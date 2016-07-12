const List = require('./List.js');

const LocalesList = List.extend({
    template: require('../../templates/static/locales-list.html'),
    data() {
        return {
            url: '/api/locales'
        };
    }
});

module.exports = LocalesList;