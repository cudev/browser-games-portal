const Vue = require('vue'),
    queryString = require('query-string'),
    superagent = require('superagent'),
    page = require('page');

const Edit = Vue.extend({
    props: ['context', 'settings'],
    data() {
        return {
            response: {
                data: {},
                included: []
            },
            url: null,
            query: {
                page: parseInt(queryString.parse(this.context.querystring).page) || 1,
                limit: parseInt(queryString.parse(this.context.querystring).limit) || 100
            },
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
    methods: {
        getData(callback, query) {
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
            superagent.del(this.url)
                .send(data)
                .query(query)
                .end(this.receiveResponse);
        },
        refresh() {
            page.redirect(`${window.location.pathname}?${queryString.stringify(this.query)}`)
            this.getData();
        },
        receiveResponse(error, response) {
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
});

module.exports = Edit;