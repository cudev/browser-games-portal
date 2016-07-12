const List = require('./List.js'),
    superagent = require('superagent');


const CategoriesList = List.extend({
    template: require('../../templates/static/categories-list.html'),
    data() {
        return {
            url: `/api/providers/${this.context.params.id}/categories`
        };
    },
    methods: {
        copyToTags() {
            superagent.post(`${this.url}/copy`)
                .send()
                .end(function (error, response) {
                    if (response.body.success) {

                    }
                });
        }
    }
});

module.exports = CategoriesList;