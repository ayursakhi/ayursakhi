define(['jquery'], function($) {
    $.widget('codazon.categorySearch', {
        options: {
            trigger: '[data-role="trigger"]',
            dropdown: '[data-role="dropdown"]',
            catList: '[data-role="category-list"]',
            activeClass: 'open',
            currentCat: false,
            allCatText: 'All Categories',
            ajaxUrl: false
        },
        _create: function() {
            this._assignVariables();
            this._assignEvents();
        },
        _assignVariables: function() {
            var self = this, conf = this.options;
            this.$trigger = this.element.find(conf.trigger);
            this.$triggerLabel = this.$trigger.children('span');
            this.$dropdown = this.element.find(conf.dropdown);
            this.$catList = this.element.find(conf.catList);
            this.$searchForm = this.element.parents('form').first();
            this.$searchForm.addClass('has-cat');
            this.$catInput = this.$searchForm.find('[name=cat]');
            this.$qInput = this.$searchForm.find('[name=q]');
            if (this.$catInput.length == 0) {
                this.$catInput = $('<input type="hidden" id="search-cat-input" name="cat">').appendTo(this.$searchForm);
            }
            if (conf.currentCat) {
                this.$catInput.val(conf.currentCat);
                var catText = this.$catList.find('[data-id=' + conf.currentCat + ']').text();
                this.$triggerLabel.text(catText);
            } else {
                this.$catInput.attr('disabled', 'disabled');
            }
            this.element.insertBefore(self.$searchForm);
        },
        _assignEvents: function() {
            var self = this, conf = this.options;
            
            $('body').on('click', '#suggest > li:first > a, .searchsuite-autocomplete .see-all', function(e) {
                e.preventDefault();
                self.$searchForm.submit();
            });
            
            this.$trigger.on('click', function() {
                self.element.toggleClass(conf.activeClass);
            });
            this.$catList.find('a').on('click', function(e) {
                e.preventDefault();
                var $cat = $(this), id = $cat.data('id'), label = $cat.text();
                if (id) {
                    self.$catInput.removeAttr('disabled').val(id).trigger('change');
                    self.$triggerLabel.text(label);
                } else {
                    self.$catInput.attr('disabled', 'disabled').val('').trigger('change');
                    self.$triggerLabel.text(conf.allCatText);
                }
                self.$qInput.trigger('input');
                self.element.removeClass(conf.activeClass);
            });
            $('body').on('click', function(e) {
                if (self.element.has($(e.target)).length == 0) {
                    self.element.removeClass(conf.activeClass);
                }
            });
        }
    });
    return $.codazon.categorySearch;
});