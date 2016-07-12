const Vue = require('vue'),
    superagent = require('superagent');

const Stats = Vue.extend({
    data() {
        return {
            statistics: []
        }
    },
    template: require('../../templates/static/stats.html'),
    activate(done) {
        const self = this;
        superagent.get('/api/stats')
            .end(function (error, response) {
                if (response.body) {
                    self.statistics = response.body;
                }
                done();
            });
    }
});

module.exports = Stats;