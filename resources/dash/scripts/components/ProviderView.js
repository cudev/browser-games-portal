const Vue = require('vue');

const ProviderView = Vue.extend({
    props: ['settings', 'context'],
    template: require('../../templates/static/provider-view.html'),
    data () {
        return {
            resource: {},
            name: 'providers'
        };
    }
});

module.exports = ProviderView;