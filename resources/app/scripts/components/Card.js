const Rating = require('./Rating.js')
const Vue = require('vue');

'use strict'

const Card = Vue.extend({
    props: ['game'],
    template: require('../../templates/static/card.html'),
    components: {
        rating: Rating
    }
});

module.exports = Card;