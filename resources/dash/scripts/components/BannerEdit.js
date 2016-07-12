const Edit = require('./Edit.js'),
    page = require('page');

const BannerEdit = Edit.extend({
    template: require('../../templates/static/banner-edit.html'),
    data() {
        return {
            url: `/api/banners/${this.context.params.id}`,
            isNew: this.context.params.id === 'new',
            response: {
                data: {
                    enabled: false,
                    priority: 1,
                    gameId: null,
                    picture: null,
                    pictureUri: null,
                    newPicture: null,
                    bannerTitles: {
                        en: {
                            translation: ''
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
                locale: locale
            }
        });
        this.response.data.bannerTitles = translations;
    },
    methods: {
        renderPicture(event) {
            const fileReader = new FileReader(),
                self = this,
                file = event.target.files[0];
            if (!file) {
                return;
            }

            fileReader.onload = function () {
                self.response.data.newPicture = this.result;
                self.response.data.pictureUri = this.result;
            }
            fileReader.readAsDataURL(file);
        },
        remove() {
            if (confirm('Are you sure?')) {
                this.deleteData();
                page.redirect('/admin/banners');
            }
        },
        receiveResponse(error, response) {
            if (!error) {
                this.response.data = response.body.data || this.response.data;
                this.response.included = response.body.included || this.response.included;

                const translations = {},
                    self = this;
                this.settings.locales.forEach(function (locale) {
                    if (!self.response.data
                        || !self.response.data.bannerTitles
                        || !self.response.data.bannerTitles[locale.language]
                        || !self.response.data.bannerTitles[locale.language].translation
                    ) {
                        translations[locale.language] = {
                            translation: '',
                            locale: locale
                        }
                    }
                });
                if (this.response.data) {
                    this.response.data.bannerTitles = Object.assign(translations, this.response.data.bannerTitles);
                } else {
                    this.response.data.bannerTitles = translations;
                }

                if (this.interactive) {
                    if (response.body.error) {
                        this.dialogue(response.body.error, false);
                    } else {
                        this.dialogue('Done!', true);
                    }
                }

                if (this.isNew && this.response.data.id) {
                    this.isNew = false;
                    this.url = `/api/banners/${this.response.data.id}`,
                        page.redirect(`/admin/banners/${this.response.data.id}`);
                }
            }
            this.isControlsDisabled = false;
        }
    }
});

module.exports = BannerEdit;