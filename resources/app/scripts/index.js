const Vue = require('vue'),
    Application = require('./components/Application.js'),
    Form = require('./components/Form.js'),
    lazyLoad = require('vue-lazyload'),
    WebFont = require('webfontloader');
require('../stylesheets/main.scss');

// Config
Vue.config.delimiters = ['${', '}'];

Vue.use(lazyLoad, {try: 3});

// Forms
var signInFormElement = document.getElementById('sign-in-form');
new Form({
    el: signInFormElement,
    data: {
        name: 'sign-in',
        fields: {
            email: {
                value: '',
                errors: {},
                isValid: true,
                element: signInFormElement.querySelector('[type="email"]')
            },
            password: {
                value: '',
                errors: {},
                isValid: true,
                element: signInFormElement.querySelector('[type="password"]')
            }
        }
    }
});

// Forms
var signUpFormElement = document.getElementById('sign-up-form');
new Form({
    el: signUpFormElement,
    data: {
        name: 'sign-up',
        fields: {
            email: {
                value: '',
                errors: {},
                isValid: true,
                element: signUpFormElement.querySelector('[name="email"]')
            },
            password: {
                value: '',
                errors: {},
                isValid: true,
                element: signUpFormElement.querySelector('[name="password"]')
            },
            passwordAgain: {
                value: '',
                errors: {},
                isValid: true,
                element: signUpFormElement.querySelector('[name="password-again"]')
            }
        }
    }
});

// Application
new Application({
    el: 'body',
    data: {
        currentModalTab: 'sign-in-form'
    }
});


// Facebook login fix
if (window.location.hash == '#_=_') {
    history.replaceState
        ? history.replaceState(null, null, window.location.href.split('#')[0])
        : window.location.hash = '';
}