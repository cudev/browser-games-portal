const List = require('./List.js'),
    superagent = require('superagent'),
    queryString = require('query-string'),
    page = require('page');

const ResourceCrawl = List.extend({
    methods: {
        save() {
            const self = this,
                query = Object.assign({}, this.query);
            query.action = 'save';
            this.postData(this.checked, query);
        },
        discard() {
            const self = this,
                query = Object.assign({}, this.query);
            query.action = 'discard';
            this.postData(this.checked, query);
        },
        crawl() {
            const self = this,
                query = Object.assign({}, this.query);
            query.action = 'crawl';
            this.postData(this.checked, query);
        },
        refresh() {
            const self = this;
            page.redirect(`${window.location.pathname}?${queryString.stringify(this.query)}`)
            this.getData();
        }
    }
});

module.exports = ResourceCrawl;