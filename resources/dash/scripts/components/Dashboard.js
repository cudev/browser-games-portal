const Vue = require('vue'),
    GamesList = require('./GamesList.js'),
    LocalesList = require('./LocalesList.js'),
    LocaleEdit = require('./LocaleEdit.js'),
    TagsList = require('./TagsList.js'),
    TagEdit = require('./TagEdit.js'),
    GameEdit = require('./GameEdit.js'),
    GamesCrawl = require('./GamesCrawl.js'),
    ProviderView = require('./ProviderView.js'),
    Translation = require('./Translation.js'),
    CategoriesList = require('./CategoriesList.js'),
    CategoryEdit = require('./CategoryEdit.js'),
    CategoriesCrawl = require('./CategoriesCrawl.js'),
    BannerList = require('./BannerList.js'),
    BannerEdit = require('./BannerEdit.js'),
    MessageBox = require('./MessageBox.js'),
    Stats = require('./Stats.js'),
    UsersList = require('./UsersList.js'),
    page = require('page');

var Dashboard = Vue.extend({
    props: {
        settings: {
            coerce: function (value) {
                return JSON.parse(value) // cast the value to Object
            }
        }
    },
    data() {
        return {
            view: 'home',
            context: {},
            message: {
                text: null,
                isSuccess: true
            }
        }
    },
    methods: {
        navigate(component) {
            const self = this;
            return function (context) {
                self.view = component;
                self.context = context;
            }
        }
    },
    ready: function () {
        page('/dash', this.navigate('home'));
        page('/admin/locales', this.navigate('localesList'));
        page('/admin/locales/:id', this.navigate('localeEdit'));
        page('/admin/users', this.navigate('users'));
        page('/admin/games', this.navigate('games'));
        page('/admin/games/:id', this.navigate('editGame'));
        page('/admin/tags', this.navigate('tags'));
        page('/admin/tags/:id', this.navigate('tagEdit'));
        page('/admin/translation', this.navigate('translation'));
        page('/admin/categories/:id', this.navigate('categoryEdit'));
        page('/admin/banners/', this.navigate('bannerList'));
        page('/admin/banners/:id', this.navigate('bannerEdit'));
        page('/admin/providers/:id', this.navigate('providerView'));
        page('/admin/providers/:id/categories', this.navigate('categoriesList'));
        page('/admin/providers/:id/categories/crawl', this.navigate('categoriesCrawl'));
        page('/admin/providers/:id/games/crawl', this.navigate('gamesCrawl'));
        page();
    },
    components: {
        home: Stats,
        games: GamesList,
        tags: TagsList,
        editGame: GameEdit,
        providerView: ProviderView,
        categoriesList: CategoriesList,
        categoriesCrawl: CategoriesCrawl,
        localesList: LocalesList,
        localeEdit: LocaleEdit,
        translation: Translation,
        tagEdit: TagEdit,
        categoryEdit: CategoryEdit,
        gamesCrawl: GamesCrawl,
        bannerList: BannerList,
        bannerEdit: BannerEdit,
        users: UsersList,
        'message-box': MessageBox
    },
    events: {
        'dialogue': function (message) {
            this.message = message;
        }
    }
});

module.exports = Dashboard;