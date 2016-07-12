const ResourceEdit = require('./ResourceEdit.js');

const CategoryEdit = ResourceEdit.extend({
    template: require('../../templates/static/category-edit.html'),
    data() {
        return {
            resource: {},
            name: 'categories',
            included: {},
            selectedTag: []
        };
    },
    methods: {
        addTag() {
            this.resource.tags = this.resource.tags || [];
            if (this.resource.tags.indexOf(this.selectedTag) === -1) {
                this.resource.tags.push(this.selectedTag);
            }
        },
        removeTag() {
            this.resource.tags.splice(this.resource.tags.indexOf(this.selectedTag), 1);
        }
    }
});

module.exports = CategoryEdit;