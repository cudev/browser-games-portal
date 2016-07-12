const List = require('./List.js'),
    queryString = require('query-string'),
    page = require('page');

const GamesList = List.extend({
    template: require('../../templates/static/games-list.html'),
    data() {
        return {
            url: '/api/games',
            isSearchExpanded: false,
            withoutDescriptions: []
        };
    },
    ready() {
        if (this.query.description) {
            this.withoutDescriptions = this.query.description.split(',').map(Number);
        }
    },
    methods: {
        search() {
            if (this.query.query === '') {
                delete this.query.query;
            }
            this.refresh();
        },
        filter() {
            if (this.withoutDescriptions.length) {
                this.query.description = this.withoutDescriptions.join(',');
            } else {
                delete this.query.description;
            }
            this.refresh();
        },
        toggleSearch() {
            this.isSearchExpanded = !this.isSearchExpanded;
        }
    }
});

module.exports = GamesList;