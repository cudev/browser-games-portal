const Edit = require('./Edit.js');

const LocaleEdit = Edit.extend({
    template: require('../../templates/static/locale-edit.html'),
    data() {
        return {
            url: `/api/locales/${this.context.params.id}`,
            isNew: this.context.params.id === 'new',
            response: {
                data: {
                    domain: ''
                }
            },
        };
    }
});

module.exports = LocaleEdit;