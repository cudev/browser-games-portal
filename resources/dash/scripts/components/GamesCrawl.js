const ResourceCrawl = require('./ResourceCrawl.js');

const GamesCrawl = ResourceCrawl.extend({
    template: require('../../templates/static/games-crawl.html'),
    data() {
        return {
            url: `/api/providers/${this.context.params.id}/games/crawl`
        };
    }
});

module.exports = GamesCrawl;