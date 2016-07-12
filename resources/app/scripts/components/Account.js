const Vue = require('vue');
const superagent = require('superagent');
const moment = require('moment');
const initials = require('../utils/initials.js');

'use strict';

let Account = Vue.extend({
    props: ['user', 'translations'],
    template: require('../../templates/static/account.html'),
    data() {
        return {
            editMode: false,
            availableDays: null,
            availableMonths: null,
            availableYears: null,
            isBirthdayChanged: false,
            imageIsTooLarge: false,
            attention: false,
            errors: {
                name: {
                    length: false,
                }
            },
            editedName: '',
            defaultDate: moment("1960-01-01", moment.ISO_8601)
        }
    },
    created() {
        const minimumYear = 1960;
        // initialize

        this.availableDays = Array.apply(null, Array(31)).map((_, i) => i + 1);
        this.availableMonths = moment.months();
        this.availableYears = Array.apply(null, Array(moment().year() - minimumYear)).map((_, i) => i + minimumYear)
        if (this.user.birthday !== null) {
            this.user.birthday = moment(this.user.birthday, moment.ISO_8601);
        } else {
            this.user.birthday = this.defaultDate;
        }
        this.editedName = this.user.name;
    },
    computed: {
        age: {
            cache: false,
            get() {
                if (this.defaultDate.isSame(this.user.birthday)) {
                    return false;
                }
                return moment().diff(this.user.birthday, 'years');
            }
        },
        day: {
            cache: false,
            get() {
                return this.user.birthday.date();
            },
            set(day) {
                this.isBirthdayChanged = true;
                this.user.birthday.date(day);
            }
        },
        month: {
            cache: false,
            get() {
                return this.user.birthday.month();
            },
            set(month) {
                this.isBirthdayChanged = true;
                this.user.birthday.month(parseInt(month));
            }
        },
        year: {
            cache: false,
            get() {
                return this.user.birthday.year();
            },
            set(year) {
                this.isBirthdayChanged = true;
                this.user.birthday.year(year);
            }
        },
        isValid: {
            cache: false,
            get() {
                const self = this;
                return Object.keys(self.errors).every(function (field) {
                    return !Object.keys(self.errors[field]).every(function (error) {
                        return !self.errors[field][error];
                    })
                });
            }
        }
    },
    methods: {
        saveProfile() {
            if (this.isValid) {
                this.attract();
                return;
            }
            const self = this;

            let data = Object.assign({}, this.user)

            data.name = this.editedName;
            if (this.isBirthdayChanged && data.birthday) {
                data.birthday = data.birthday.format('YYYY-MM-DD');
            }

            superagent.post(window.location.pathname).send(data).end();
            this.toggleEditMode();
        },
        editProfile() {
            this.toggleEditMode();
        },
        toggleEditMode() {
            this.editMode = !this.editMode
        },
        uploadPicture(event) {
            const self = this,
                picture = event.target.files[0];

            if (!picture) {
                return;
            }

            const pictureSize = parseFloat(picture.size / 1024).toFixed(2);

            if (pictureSize > 2048) {
                event.target.value = '';
                this.imageIsTooLarge = true;
                return;
            }
            this.imageIsTooLarge = false;

            let formData = new FormData();
            formData.append('picture', picture);
            superagent.post(window.location.pathname + '/picture')
                .send(formData)
                .end(function (error, response) {
                    console.log(response);
                    if (!error && response.body.success && response.body.data.picture) {
                        self.user.pictureUrl = response.body.data.picture;
                    } else if (response.status === 413) {
                        event.target.value = '';
                        this.imageIsTooLarge = true;
                    }
                });
        },
        validate() {
            this.errors.name.length = this.editedName.trim().length > 30 || this.editedName.trim().length < 3;
        },
        attract() {
            const self = this;

            self.attention = true;
            setTimeout(
                function () {
                    self.attention = false;
                },
                500
            );
        },
        initials(name) {
            return initials(name);
        },
        removePicture() {
            const self = this;
            superagent.del(window.location.pathname + '/picture')
                .end(function (error, response) {
                    if (!error && !response.body.errors) {
                        self.user.pictureUrl = null;
                    }
                });
        }
    }
});

module.exports = Account;