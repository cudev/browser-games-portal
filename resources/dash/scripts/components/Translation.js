const List = require('./List.js');

const Translation = List.extend({
    template: require('../../templates/static/translation.html'),
    data() {
        return {
            url: '/api/static-content'
        };
    },
});

module.exports = Translation;