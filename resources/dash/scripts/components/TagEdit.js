const Edit = require('./Edit.js');

const TagEdit = Edit.extend({
    template: require('../../templates/static/tag-edit.html'),
    data() {
        return {
            url: `/api/tags/${this.context.params.id}`,
            isNew: this.context.params.id === 'new',
            response: {
                data: {
                    enabled: false,
                    featured: false,
                    tagNames: {
                        en: {
                            translation: '',
                            slug: '',
                            description: ''
                        }
                    }
                }
            }
        }
    },
    created() {
        const translations = {};
        this.settings.locales.forEach(function (locale) {
            translations[locale.language] = {
                translation: '',
                slug: '',
                description: '',
                locale: locale
            }
        });
        this.response.data.tagNames = translations;
    },
    methods: {
        receiveResponse(error, response) {
            if (!error) {
                this.response = response.body || this.response;
                const translations = {},
                    self = this;

                this.settings.locales.forEach(function (locale) {
                    if (!self.response.data.tagNames
                        || !self.response.data.tagNames[locale.language]
                        || !self.response.data.tagNames[locale.language].translation
                    ) {
                        translations[locale.language] = {
                            translation: '',
                            slug: '',
                            description: '',
                            locale: locale
                        }
                    }
                });
                this.response.data.tagNames = Object.assign(translations, this.response.data.tagNames);

                if (this.interactive) {
                    if (response.body.error) {
                        this.dialogue(response.body.error, false);
                    } else {
                        this.dialogue('Done!', true);
                    }
                }
            }
        },
        remove() {
            if (confirm('Are you sure?')) {
                this.deleteData(false);
            }
        },
        generateSlug(tagName) {
            tagName.slug = slugify(tagName.translation);
        }
    }
});

function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start of text
        .replace(/-+$/, '');            // Trim - from end of text
}

module.exports = TagEdit;