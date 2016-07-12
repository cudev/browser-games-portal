const Account = require('./Account.js'),
    Rating = require('./Rating.js'),
    Comments = require('./Comments.js'),
    superagent = require('superagent'),
    GameTabs = require('./GameTabs.js'),
    screenfull = require('screenfull'),
    initials = require('../utils/initials.js'),
    Vue = require('vue'),
    Subscription = require('./Subscription.js');

'use strict'

// TODO: decouple into small components (e.g. Player, Authentication, etc.)
const Application = Vue.extend({
    props: {
        user: {
            coerce: function (val) {
                return JSON.parse(val) // cast the value to Object
            }
        }
    },
    data () {
        return {
            // popup
            isAuthModalVisible: false,
            currentModalTab: '',

            // categories
            isCategoriesVisible: false,

            // game player
            isInfoVisible: false,
            isScreenfullEnabled: screenfull.enabled,
            visiblePlayerTabs: {
                info: false,
                share: false
            },
            isPlayerCovered: !this.user,
        };
    },
    methods: {
        toggleAuthModal(subModal) {
            this.currentModalTab = subModal;
            this.isAuthModalVisible = !this.isAuthModalVisible;

            if (this.isAuthModalVisible && ga) {
                ga('send', {
                    hitType: 'pageview',
                    page: `${location.pathname}/auth-modal`
                });
            }

            document.querySelector('body').classList.toggle('u-Modal--unscroll');
        },
        signOut() {
            if (ga) {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'User',
                    eventAction: 'sign-out',
                    eventLabel: location.pathname,
                    nonInteraction: true
                });
            }
            superagent.post('/user/sign-out').end(function (error, response) {
                window.location.reload();
            });
        },
        toggleCategories() {
            this.isCategoriesVisible = !this.isCategoriesVisible;

            if (this.isCategoriesVisible && ga) {
                ga('send', {
                    hitType: 'pageview',
                    page: `${location.pathname}/categories`
                });
            }
        },
        toggleBookmark(event) {
            if (!this.user) {
                this.toggleAuthModal('sign-up-form');
                return;
            }
            event.target.classList.toggle('is-bookmarked');
            let data = {
                gameId: event.target.dataset.actionBookmark
            };
            superagent.post('/bookmark')
                .send(data)
                .end(function (error, response) {
                    // todo: process response
                });
        },
        togglePlayerTab(tab) {
            if (this.isPlayerCovered) {
                return;
            }
            const self = this;
            Object.keys(this.visiblePlayerTabs).forEach(function (key) {
                self.visiblePlayerTabs[key] = key === tab ? !self.visiblePlayerTabs[key] : false;

                if (key === tab && self.visiblePlayerTabs[key] && ga) {
                    ga('send', {
                        hitType: 'pageview',
                        page: `${location.pathname}/${tab}`
                    });
                }

            });
        },
        bannerClicked(gameSlug) {
            if (ga) {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'Banner',
                    eventAction: gameSlug,
                    eventLabel: location.pathname,
                    nonInteraction: true
                });
            }
        },
        goFullScreen() {
            if (this.isPlayerCovered) {
                return;
            }

            if (ga) {
                ga('send', {
                    hitType: 'event',
                    eventCategory: 'User',
                    eventAction: 'fullscreen',
                    eventLabel: location.pathname,
                    nonInteraction: true
                });
            }

            screenfull.toggle(document.getElementsByTagName('iframe')[0]);
        },
        initials(name) {
            return initials(name);
        },
        uncover() {
            this.isPlayerCovered = false;
        }
    },
    components: {
        account: Account,
        rating: Rating,
        'game-tabs': GameTabs,
        comments: Comments,
        'footer-subscription': Subscription.extend({
            template: require('../../templates/static/footer-subscribe.html')
        }),
        'player-subscription': Subscription.extend({
            template: require('../../templates/static/player-cover-subscribe.html')
        })
    },
    events: {
        modal(modalName) {
            this.toggleAuthModal(modalName);
        },
        subscribed(success) {
            this.isPlayerCovered = !success;
        }
    }
});

module.exports = Application;