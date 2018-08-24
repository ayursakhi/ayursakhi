define([
    'jquery',
    'jquery/ui'
], function($){
    $.widget('codazon.lookbookmenu', {
        options: {
            item: 'li',
            link: 'a.menu-link',
            subCat: 'ul',
            toggleClass: 'dropdown-toggle',
            openClass: 'open',
            currentItem: '.current-item'
        },
        _create: function() {
            this._bindEvents();
            this._openCurrentItem();
        },
        _bindEvents: function() {
            var self = this, conf = this.options;
            this.element.find(conf.link).each(function() {
                var $link = $(this), $item = $link.parents(conf.item).first(), $subCat = $item.children(conf.subCat).first();
                if ($subCat.length) {
                    var $toggle = $('<div class="' + conf.toggleClass + '"></div>').insertAfter($link);
                    $toggle.on('click', function() {
                        $item.toggleClass(conf.openClass);
                        $subCat.slideToggle(300);
                    });
                }
            });
        },
        _openCurrentItem: function() {
            var self = this, conf = this.options;
            var $curentItem = $(conf.currentItem, this.element);
            if ($curentItem.length) {
                $curentItem.parents(conf.subCat).each(function() {
                    var $subCat = $(this);
                    if (self.element.has($subCat).length) {
                        $subCat.show();
                        $subCat.parents(conf.item).first().addClass(conf.openClass);
                    }
                });
            }
        }
    });
    return $.codazon.lookbookmenu;
});