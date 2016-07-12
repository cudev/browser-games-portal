var superagent = require('superagent');
var Vue = require('vue');
'use strict'

var Form = Vue.extend({
    data: function () {
        return {
            name: '',
            fields: {}
        }
    },
    methods: {
        submit: function (action) {
            var fields = this.fields,
                data = {},
                self = this;

            if (ga) {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'User',
                    eventAction: this.name,
                    eventLabel: location.pathname,
                    nonInteraction: true
                });
            }

            Object.keys(fields).map(key => data[key] = fields[key].value);
            superagent.post(action).send(data).end(function (error, response) {
                if (!error && response.body.success) {
                    window.location.reload();
                }
                Object.keys(response.body.errors).forEach(function (key) {
                    fields[key].errors = response.body.errors[key];
                });
                self.updateConstraints();
            })
        },
        updateConstraints: function () {
            var fields = this.fields;
            Object.keys(fields).forEach(function (key) {
                var errors = fields[key].errors;
                fields[key].isValid = Object.keys(errors).every(key => !errors[key]);
            });
        }
    }
});

module.exports = Form;