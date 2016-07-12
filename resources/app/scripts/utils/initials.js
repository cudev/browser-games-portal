'use strict'

const initials = function (name) {
    return name.split(' ').map(s => s.charAt(0)).join('').substr(0, 2).toUpperCase();
}

module.exports = initials;