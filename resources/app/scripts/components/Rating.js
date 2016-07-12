const superagent = require('superagent'),
    Vue = require('vue');

'use strict'

const Rating = Vue.extend({
    template: require('../../templates/static/rating.html'),
    props: {
        rating: {
            type: Number,
            default: 0
        },
        total: {
            type: Number,
            default: 5
        },
        readonly: {
            type: Boolean,
            default: false
        },
        gameId: {
            type: Number
        },
        user: Object
    },
    data: function () {
        return {
            hover: 0
        }
    },
    methods: {
        setRating: function (rating) {
            if (this.readonly) {
                return;
            }

            if (ga) {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'User',
                    eventAction: 'rate',
                    eventLabel: location.pathname,
                    eventValue: rating,
                    nonInteraction: true
                });
            }

            if (!this.user) {
                this.$dispatch('modal', 'sign-up-form');
                return;
            }
            this.rating = rating;
            var data = {
                gameId: this.gameId,
                rating: rating
            };
            superagent.post('/rate').send(data).end(function (error, response) {

            })
        },
        setHover: function (hover) {
            if (this.readonly) {
                return;
            }
            this.hover = hover;
        }
    }
});

module.exports = Rating;