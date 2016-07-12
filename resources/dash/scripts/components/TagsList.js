const List = require('./List.js');

const TagsList = List.extend({
    template: require('../../templates/static/tags-list.html'),
    data() {
        return {
            url: '/api/tags'
        };
    }
});

module.exports = TagsList;