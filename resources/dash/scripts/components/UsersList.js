const List = require('./List.js'),
    superagent = require('superagent');


const UsersList = List.extend({
    template: require('../../templates/static/users-list.html'),
    data() {
        return {
            url: '/api/users'
        };
    }
});

module.exports = UsersList;