const Edit = require('./Edit.js');

const GameEdit = Edit.extend({
    template: require('../../templates/static/game-edit.html'),
    data() {
        return {
            url: `/api/games/${this.context.params.id}`,
            isNew: this.context.params.id === 'new',
            response: {
                data: {
                    enabled: false,
                    name: null,
                    slug: null,
                    type: null,
                    url: null,
                    width: null,
                    height: null,
                    tags: [],
                    id: null,
                    thumbnail: null,
                    descriptions: {
                        en: {
                            translation: ''
                        }
                    }
                }
            },
            selectedTag: null
        }
    },
    created() {
        const translations = {};
        this.settings.locales.forEach(function (locale) {
            translations[locale.language] = {
                translation: '',
                locale: locale
            }
        });
        this.response.data.descriptions = translations;
    },
    methods: {
        receiveResponse(error, response) {
            if (!error) {
                this.response = response.body || this.response;

                const translations = {},
                    self = this;
                this.settings.locales.forEach(function (locale) {
                    if (!self.response.data.descriptions
                        || !self.response.data.descriptions[locale.language]
                        || self.response.data.descriptions[locale.language].translation === 'undefined'
                        || self.response.data.descriptions[locale.language].translation === null
                    ) {
                        translations[locale.language] = {
                            translation: '',
                            locale: locale
                        }
                    }
                });
                this.response.data.descriptions = Object.assign(translations, this.response.data.descriptions);

                if (this.interactive) {
                    if (response.body.error) {
                        this.dialogue(response.body.error, false);
                    } else {
                        this.dialogue('Done!', true);
                    }
                }
            }
        },
        addTag() {
            if (this.response.data.tags.indexOf(this.selectedTag) === -1) {
                this.response.data.tags.push(this.selectedTag);
            }
        },
        removeTag(tag) {
            this.response.data.tags.splice(this.response.data.tags.indexOf(tag), 1);
        }
    }
});

module.exports = GameEdit;