const Vue = require('vue'),
    superagent = require('superagent');

const ResourceEdit = Vue.extend({
    props: ['context', 'settings'],
    data() {
        return {
            resource: {},
            included: {},
            name: ''
        }
    },
    activate (done) {
        var self = this,
            id = this.context.params.id;

        // if url looks like "http://domain.com/:resources/new" we go into create mode
        if (this.context.params.id !== 'new') {
            superagent.get('/api/' + this.name + '/' + this.context.params.id).end(function (error, response) {
                if (!error) {
                    self.resource = self.prepareResource(response.body.data)
                    self.included = response.body.included || {}
                }
                done()
            });
        } else {
            setTimeout(function () {
                done();
            }, 0);
        }
    },
    methods: {
        submit () {
            if (this.context.params.id !== 'new') {
                const url = '/api/' + this.name + '/' + this.context.params.id
                superagent.patch(url)
                    .send(this.beforeSend(this.resource))
                    .end(function (error, response) {
                        console.log(arguments)
                    });
            } else {
                this.create();
            }
        },
        remove () {
            if (confirm('Are you sure?')) {
                let url = '/api/' + this.name + '/' + this.context.params.id;
                superagent.del(url)
                    .end(function (error, response) {
                        console.log(arguments)
                    });
            }
        },
        create () {
            let url = '/api/' + this.name + '/' + this.context.params.id;
            const self = this;
            superagent.post(url)
                .send(this.beforeSend(this.resource))
                .end(function (error, response) {
                    if (response.body.data.id) {
                        page.redirect('/admin/' + self.name + '/' + response.body.data.id)
                    }
                    console.log(arguments)
                });
        },

        prepareResource(resource) {
            return resource;
        },
        beforeSend(resource) {
            return resource;
        }
    }
});

module.exports = ResourceEdit;