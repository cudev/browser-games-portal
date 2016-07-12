const Vue = require('vue'),
    Pagination = require('./Pagination.js'),
    queryString = require('query-string'),
    superagent = require('superagent'),
    page = require('page');

const List = Vue.extend({
    props: ['context', 'settings'],
    data() {
        return {
            response: {
                data: []
            },
            checked: [],
            url: null,
            query: queryString.parse(this.context.querystring),
            interactive: false,
            isControlsDisabled: false
        }
    },
    activate(done) {
        const self = this;
        this.getData(function () {
            done();
            self.interactive = true
        });
    },
    created() {
        this.query.page = this.query.page || 1;
    },
    methods: {
        getData(callback, query) {
            this.isControlsDisabled = true;

            const self = this,
                wrapper = function (error, response) {
                    self.receiveResponse(error, response);
                    if (callback) {
                        callback()
                    }
                };
            query = query || this.query;
            superagent.get(this.url)
                .query(query)
                .end(wrapper);
        },
        putData(data, query) {
            this.isControlsDisabled = true;

            const self = this;
            query = query || this.query;
            data = data || this.response.data;
            superagent.put(this.url)
                .send(data)
                .query(query)
                .end(this.receiveResponse);
        },
        postData(data, query) {
            this.isControlsDisabled = true;

            const self = this;
            query = query || this.query;
            data = data || this.response.data;
            superagent.post(this.url)
                .send(data)
                .query(query)
                .end(this.receiveResponse);
        },
        deleteData(data, query) {
            this.isControlsDisabled = true;

            const self = this;
            query = query || this.query;
            data = data || this.checked;
            superagent.post(this.url)
                .send(data)
                .query(query)
                .end(this.receiveResponse);
        },
        allChecked() {
            return this.checked.length === this.response.data.length;
        },
        checkAll() {
            this.checked = this.response.data.map(resource => resource.cacheKey);
        },
        uncheckAll() {
            this.checked = [];
        },
        checkToggle() {
            const allChecked = this.allChecked();
            this.uncheckAll();
            if (!allChecked) {
                this.checkAll()
            }
        },
        refresh() {
            page.redirect(`${window.location.pathname}?${queryString.stringify(this.query)}`)
            this.getData();
        },
        receiveResponse(error, response) {
            this.uncheckAll();
            if (!error) {
                this.response = response.body || this.response;
                if (this.interactive) {
                    if (response.body.error) {
                        this.dialogue(response.body.error, false);
                    } else {
                        this.dialogue('Done!', true);
                    }
                }
            }
            this.isControlsDisabled = false;
        },
        dialogue(text, isSuccess) {
            this.$dispatch('dialogue', {
                text: text,
                isSuccess: isSuccess
            });
        }
    },
    components: {
        pagination: Pagination
    },
    events: {
        'page-changed': function (pageNumber) {
            this.query.page = pageNumber;
            this.refresh();
        }
    }
});

module.exports = List;