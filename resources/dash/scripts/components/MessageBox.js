const Vue = require('vue');

const MessageBox = Vue.extend({
    template: require('../../templates/static/message-box.html'),
    props: ['text', 'isSuccess']
});

module.exports = MessageBox;