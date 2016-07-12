const Vue = require('vue');

const Pagination = Vue.extend({
    props: ['current', 'total'],
    template: require('../../templates/static/pagination.html'),
    methods: {
        notifyChanges(changed, value) {
            this.$dispatch(`${changed}-changed`, value)
        },
        pages() {
            return this.paginator(this.current, this.total);
        },
        paginator(current, total) {
            if (!total) {
                return [null];
            }
            var sequence = 7;
            var amplitude = Math.floor(sequence / 2);

            if (total <= sequence) {
                return this.range(1, total);
            }
            var pagination = [];

            if (current >= 5 && current <= total - 4) {
                pagination.push(1);
                pagination.push(null);
                pagination.push.apply(pagination, this.range(current - 1, current + 1))
                pagination.push(null);
                pagination.push(total);
                return pagination;
            }

            if (current <= 5) {
                pagination.push.apply(pagination, this.range(1, 5))
                pagination.push(null);
                pagination.push(total);
                return pagination;
            }

            if (current >= total - 4) {
                pagination.push(1);
                pagination.push(null);
                pagination.push.apply(pagination, this.range(total - 4, total))
                return pagination;
            }
        },
        range(start, end, stepSize) {
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
    }
});

module.exports = Pagination;