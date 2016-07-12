const superagent = require('superagent'),
    Vue = require('vue'),
    initials = require('../utils/initials.js');

'use strict';

const Comments = Vue.extend({
    props: {
        user: Object,
        translations: Object
    },
    template: require('../../templates/static/comments.html'),
    data() {
        return {
            newComment: null,
            currentPage: null,
            perPage: 4,
            comments: [],
            totalPages: 0,
            allComments: [],
            editing: false,
            edited: '',
            editingRows: 5
        }
    },
    created() {
        var self = this;
        superagent.get(window.location.pathname + '/comment').end(function (error, response) {
            self.allComments = response.body.data;
            self.allComments = self.allComments.reverse();
            self.totalPages = self.countTotalPages(self.allComments.length, self.perPage)
            self.turnPage(1);
        });

    },
    computed: {
        pagination() {
            var pagination = paginator(this.currentPage, this.totalPages).map(function (value, index) {
                return {
                    page: value,
                    isSkipped: !value
                }
            });
            return pagination;
        }
    },
    methods: {
        addComment() {
            var text = this.newComment.trim();
            if (text) {
                const self = this;
                const comment = {
                    body: text
                };

                if (ga) {
                    ga('send', {
                        hitType: 'event',
                        eventCategory: 'User',
                        eventAction: 'comment',
                        eventLabel: location.pathname,
                        nonInteraction: true
                    });
                }

                superagent.post(window.location.pathname + '/comment')
                    .send(comment)
                    .end(function (error, response) {
                        self.allComments.unshift(response.body.data);
                        self.newComment = '';
                        self.totalPages = self.countTotalPages(self.allComments.length, self.perPage)
                        self.turnPage(1);
                    });
            }
        },
        turnPage(pageNumber) {
            if (pageNumber < 1 || pageNumber > this.totalPages) {
                return;
            }

            if (ga) {
                ga('send', {
                    hitType: 'pageview',
                    page: `${location.pathname}/comments/${pageNumber}`
                });
            }

            let from = (pageNumber - 1) * this.perPage;
            let to = from + this.perPage;
            this.currentPage = pageNumber;
            this.comments = this.allComments.slice(from, to);
        },
        turnPageForwards() {
            this.turnPage(this.currentPage + 1);
        },
        turnPageBackwards() {
            this.turnPage(this.currentPage - 1);
        },
        countTotalPages(itemsLength, itemsPerPage) {
            return Math.ceil(itemsLength / itemsPerPage);
        },
        notifyToOpenModal(modalName) {
            this.$dispatch('modal', modalName);
        },
        updateComment(comment) {
            if (!this.edited.trim()) {
                return;
            }

            if (this.edited.trim() === comment.body) {
                this.closeEditor();
                return;
            }

            const self = this;
            comment.body = this.edited;
            superagent.patch(window.location.pathname + '/comment')
                .send(comment)
                .end(function (error, response) {

                });

            this.closeEditor();
        },
        openEditor(comment) {
            this.edited = comment.body;
            this.editing = comment.id;
            this.updateSize();
        },
        closeEditor() {
            this.editing = false;
        },
        initials(name) {
            return initials(name)
        },
        updateSize() {
            const newLines = this.edited.split(/\r\n|\r|\n/).length;
            this.editingRows = Math.ceil((this.edited.length - newLines) / 100) + newLines;
        }
    }
});

function paginator(current, total) {
    if (!total) {
        return [null];
    }
    var sequence = 7;

    if (total <= sequence) {
        return range(1, total);
    }

    var pagination = [];

    if (current >= 5 && current <= total - 4) {
        pagination.push(1);
        pagination.push(null);
        pagination.push.apply(pagination, range(current - 1, current + 1))
        pagination.push(null);
        pagination.push(total);
        return pagination;
    }

    if (current <= 5) {
        pagination.push.apply(pagination, range(1, 5))
        pagination.push(null);
        pagination.push(total);
        return pagination;
    }

    if (current >= total - 4) {
        pagination.push(1);
        pagination.push(null);
        pagination.push.apply(pagination, range(total - 4, total))
        return pagination;
    }
}

function range(start, end, stepSize) {
    if (start > end) {
        return [];
    }
    var stepSize = stepSize || 1;
    const length = Math.floor((end - start) / stepSize) + 1;
    return Array.apply(0, Array(length)).map(function (element, index) {
            return start + (index * stepSize);
        }
    );
}

module.exports = Comments;