const List = require('./List.js');

const BannerList = List.extend({
    template: require('../../templates/static/banners-list.html'),
    data() {
        return {
            url: '/api/banners'
        };
    }
});

module.exports = BannerList;