const superagent = require('superagent'),
    Card = require('./Card.js'),
    Vue = require('vue');

'use strict';

const GameTabs = Vue.extend({
    props: ['translations'],
    template: require('../../templates/static/game-tabs.html'),
    components: {
        card: Card
    },
    data() {
        return {
            activeTab: '',
            bookmarkedGames: [],
            playedGames: []
        }
    },
    created: function () {
        this.changeTab('bookmarked')
    },
    methods: {
        changeTab: function (tabName) {
            var self = this;
            if (self[tabName + 'Games'] !== 'undefined') {
                superagent.get('/user/games?filter=' + tabName).end(function (error, response) {
                    if (!error && response.body.success) {
                        self[tabName + 'Games'] = response.body.data
                    }
                });
            }
            this.activeTab = tabName;
        }
    }
});

module.exports = GameTabs;