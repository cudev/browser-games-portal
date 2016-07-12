const ResourceCrawl = require('./ResourceCrawl.js');

const CategoriesCrawl = ResourceCrawl.extend({
    template: require('../../templates/static/categories-crawl.html'),
    data () {
        return {
            url: `/api/providers/${this.context.params.id}/categories/crawl`
        };
    }
});

module.exports = CategoriesCrawl;