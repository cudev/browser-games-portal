const Vue = require('vue'),
    superagent = require('superagent');

'use strict'

const Subscription = Vue.extend({
    props: ['translations'],
    data() {
        return {
            isSubscribed: false,
            isValid: false,
            showError: false,
            emailAddress: '',
        }
    },
    methods: {
        subscribe() {
            const self = this,
                address = this.emailAddress;

            if (!this.isValid) {
                this.showError = true;
                return;
            }

            if (ga) {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'User',
                    eventAction: 'subscribe',
                    eventLabel: location.pathname,
                    nonInteraction: true
                });
            }

            superagent.post('/user/subscribe')
                .send({email: address})
                .end(function (error, response) {
                    if (!error && response.body) {
                        self.isSubscribed = response.body.success;
                        self.isValid = response.body.success;
                        self.showError = !self.isValid;
                        self.notifySubsciption(self.isSubscribed)
                    }
                });
        },
        validate(event) {
            this.isValid = event.target.validity.valid;
            this.showError = false;
        },
        notifySubsciption(success) {
            this.$dispatch('subscribed', success)
        }
    }
});

module.exports = Subscription;