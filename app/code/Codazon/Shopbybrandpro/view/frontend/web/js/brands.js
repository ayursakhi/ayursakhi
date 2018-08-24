/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'jquery/jquery-ui-1.9.2', 'owlslider'], function($) {
    var rtl = $('body').hasClass('rtl-layout');
    
    $.widget('codazon.autowidth', {
        options: {
            item: '[data-role=item]',
            itemsPerRow: []
        },
        _create: function() {
            var self = this, conf = this.options;
            if (!conf.itemsPerRow) {
                return true;
            }
            
            var i = 0;
            self.itemsPerRow = [];
            for(var point in conf.itemsPerRow) {
                self.itemsPerRow[i] = {};
                self.itemsPerRow[i]['breakPoint'] = point;
                self.itemsPerRow[i]['items'] = conf.itemsPerRow[point];
                i++;
            };
            self._setWidth();
            self.element.parents('[data-role="static-list-wrapper"]').removeClass('no-loaded').find('[data-role="static-list-loader"]').remove();
            var ww = window.innerWidth, t = false;
            $(window).resize(function() {
                if(t) clearTimeout(t);
                t = setTimeout(function() {
                    if (window.innerWidth != ww) {
                        self._setWidth();
                        ww = window.innerWidth;
                    }
                },50);
            });
            $('body').on('contentUpdated', function() {
                self._setWidth();
            });
        },
        _getBreakPoint: function() {
            var self = this, conf = this.options;
            var ww = window.innerWidth, breakPoint = 0, bpLength = self.itemsPerRow.length;
            for(var i = bpLength - 1; i >=0; i--) {
                if (self.itemsPerRow[i].breakPoint <= ww) {
                    breakPoint = self.itemsPerRow[i].breakPoint; break;
                }
            };
            return breakPoint;
        },
        _setWidth: function(itemsPerRow) {
            var self = this, conf = this.options;
            var bp = self._getBreakPoint();
            if (conf.itemsPerRow[bp] > 0) {
                var itemWidth = 100/conf.itemsPerRow[bp];
                self.element.find(conf.item).each(function() {
                    var $item = $(this);
                    $item.css({width: itemWidth + '%'});
                });
            }
        }
    });
    
    $.widget('codazon.brandslider', {
        options: {
            mbMargin: 10
        },
        _create: function() {
            var self = this, conf = this.options;
            if (typeof conf.sliderConfig.responsive !== 'undefined') {
                $.each([768, 480, 320, 0], function(i, breakPoint) {
                    if (typeof conf.sliderConfig.responsive[breakPoint] !== 'undefined') {
                        conf.sliderConfig.responsive[breakPoint] = $.extend({}, {margin: conf.mbMargin}, conf.sliderConfig.responsive[breakPoint]);
                    }
                });
            }
            self.element.addClass('owl-carousel');
            conf.sliderConfig.rtl = rtl;
            conf.sliderConfig.lazyLoad = true;
            self.element.parents('[data-role="slider-wrapper"]').removeClass('no-loaded').find('[data-role="slider-loader"]').remove();
            self.element.owlCarousel(conf.sliderConfig);
        }
    });
    $.widget('codazon.alphabetList', {
        options: {
            charList: '[data-role="char-list"]',
            brandList: '[data-role="brand-list"]',
            charItem: '[data-char]',
            item: '[data-label]',
            noItemLabel: '.no-item',
            sameHeight: '.item-bottom',
        },
        _create: function() {
            var self = this, conf = this.options;
            this._assignVariables();
            this._arrangeList();
            self.element.removeClass('no-loaded');
            self.element.find('.brand-inner').removeClass('hidden');
            self._lazyImage();
            self._sameHeight();
            var winWidth = window.innerWidth, t = false;
            $(window).on('resize', function() {
                if (window.innerWidth != winWidth) {
                    if (t) {
                        clearTimeout(t);
                    }
                    t = setTimeout(function() {
                        self._sameHeight();
                    }, 300);
                    winWidth = window.innerWidth;
                }
            });
        },
        _lazyImage: function() {
            var self = this, conf = this.options;
            self.element.find('[data-src]').each(function() {
                var $img = $(this);
                $img.attr('src', $img.data('src'));
            });
        },
        _sameHeight: function() {
            var self = this, conf = this.options;
            self.element.find('.brand-group').each(function() {
                var maxHeight = 0, $group = $(this);
                $group.find(conf.sameHeight).css({minHeight: ''}).each(function() {
                    var $sItem = $(this);
                    var height = $sItem.outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                }).css({minHeight: maxHeight});
            });
        },
        _assignVariables: function() {
            var self = this, conf = this.options;
            self.$charList = self.element.find(conf.charList);
            self.$brandList = self.element.find(conf.brandList);
            self.$items = self.element.find(conf.item);
            self.$charItem = self.element.find(conf.charItem);
            self.$noItemLabel = self.element.find(conf.noItemLabel);
            self.brandGroups = {};
            self.$items.each(function() {
                var $item = $(this);
                var firstChar = $item.data('label')[0];
                if (typeof self.brandGroups[firstChar] == 'undefined') {
                    self.brandGroups[firstChar] = [];
                }
                self.brandGroups[firstChar].push($item);
            });
            self._filterList();
        },
        _arrangeList: function() {
            var self = this, conf = this.options;
            $.each(self.brandGroups, function(character, el) {
                var $group = $('<div class="brand-group" data-group="'+ character +'">');
                $group.append('<div class="group-header"><div class="label">' + character + '</div></div>');
                var $items = $('<div class="items row"></div>');
                $items.appendTo($group);
                $.each(self.brandGroups[character], function(i, $item) {
                    $item.appendTo($items);
                });
                $group.appendTo(self.$brandList);
                self.$charList.find('[data-char="'+character+'"]').addClass('available');
            });
            self.$charList.find('[data-char=all]').addClass('available');
            var $target;
            for(var character = 0; character <= 9; character++) {
                $target = self.element.find('[data-group="'+character+'"]');
                if ($target.length) {
                     self.$charList.find('[data-char=num]').addClass('available');
                     break;
                }
            }
        },
        _filterList: function() {
            var self = this, conf = this.options;
            self.element.find('[data-char]').click(function(e) {
                e.preventDefault();
                var $char = $(this), character = $char.data('char');
                if (!$char.hasClass('available')) {
                    return true;
                }
                $char.addClass('active').siblings().removeClass('active');
                if (character == 'all') {
                    self.element.find('[data-group]').show();
                    self.$noItemLabel.addClass('hidden');
                } else if (character == 'num') {
                    var $target, found = false;
                    for(var character = 0; character <= 9; character++) {
                        $target = self.element.find('[data-group="'+character+'"]');
                        if ($target.length) {
                            $target.show().siblings().hide();
                            self.$noItemLabel.addClass('hidden');
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        self.element.find('[data-group]').hide();
                        self.$noItemLabel.removeClass('hidden');
                    }
                } else {
                    var $target = self.element.find('[data-group="'+character+'"]');
                    if ($target.length) {
                        $target.show().siblings().hide();
                        self.$noItemLabel.addClass('hidden');
                    } else {
                        self.element.find('[data-group]').hide();
                        self.$noItemLabel.removeClass('hidden');
                    }
                }
                self._sameHeight();
            });
        }
    });
    $.widget('codazon.searchBrands', {
        options: {
            input: '[data-role=brand_name]',
            sourceUrl: false,
            brandList: [],
            appendTo: '[data-role=list-wrap]',
            brandUrl: false,
        },
        _create: function() {
            var self = this, conf = this.options;
            this.$input = $(conf.input, self.element);
            this.$appendTo = $(conf.appendTo, self.element);
            $.ajax({
                url: conf.brandUrl,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    self.element.removeClass('hidden');
                    self.$input.autocomplete({
                        source: res,
                        appendTo: self.$appendTo,
                        autoFocus: true,
                        messages: {
                            noResults: conf.noResults,
                            results: function (amount) {
                                if (amount > 1) {
                                    return conf.resultsP.replace(/%1/gi, amount);
                                } else {
                                    return conf.resultsS.replace(/%1/gi, amount);
                                }
                            }
                        },
                        focus: function(event, ui) {
                            var $a = $('.ui-state-focus', self.$appendTo);
                            $a.parents('.item').first().addClass('selected').siblings().removeClass('selected');
                        },
                        // close: function(event, ui) {
                            // var $selected = self.$appendTo.find('li.selected');
                                // if ($selected.length) {
                                // $selected.removeClass('selected');
                                // if (self.element.find('.ui-helper-hidden-accessible.has-items')) {
                                    // self.$input.val($selected.find('.brand-label').text());
                                    // self.$input.parents('form').attr('action', $selected.find('a').attr('href'));
                                // } else {
                                    // self.$input.parents('form').removeAttr('action');
                                // }
                            // }
                        // }
                    });
                    var uiAutocomplete = self.$input.data('uiAutocomplete');
                    uiAutocomplete._renderItem = function( ul, item ) {
                        ul.addClass('brand-list');
                        var label = item.label, inputText = self.$input.val();
                        if (inputText) {
                            var re = new RegExp(inputText,"gi");
                            label = label.replace(re, function(match){
                                return '<strong>' + match + '</strong>';
                            });
                        }
                        var html = '';
                        html += '<a href="' + item.url +'">';
                        html +=     '<span class="brand-img"><img src="' + item.img + '" /></span>';
                        html +=     '<span class="brand-label">' + label + '</span>';
                        html += '</a>';
                        return $('<li class="item">')
                            .append(html)
                            .appendTo(ul);
                    };
                    uiAutocomplete.__responseOld = uiAutocomplete.__response;
                    uiAutocomplete.__response = function(content) {
                        var that = uiAutocomplete;
                        that.__responseOld(content);
                        if (content && content.length) {
                            that.liveRegion.addClass('has-items');
                            self.$appendTo.find('.brand-list').removeClass('_hide');
                        } else {
                            self.$appendTo.find('.brand-list').addClass('_hide');
                            self.$appendTo.find('li.selected').removeClass('selected');
                            that.liveRegion.removeClass('has-items');
                        }
                    }
                }
            });
            this.$input.on('focus', function() {
                $('.brand-list', self.element).show();
                if ($('.has-items', self.element).length) {
                    $('.brand-list', self.element).removeClass('_hide');
                }
            }).on('blur', function() {
                //if (self.$input.val() == '') {
                    self.$appendTo.find('.brand-list').addClass('_hide');
                    self.element.find('.ui-helper-hidden-accessible').text('');
                //}
            });
        }
    });
    
    $.widget('codazon.brands', {
        options: {
            
        },
        _create: function(){
            var self = this;
            $.each(this.options, function(fn, options){
                var namespace = fn.split(".")[0];
                var name = fn.split(".")[1];
                if (typeof $[namespace] !== 'undefined') {
                    if(typeof $[namespace][name] !== 'undefined') {
                        $[namespace][name](options, self.element);
                    }
                }
            });
        }
    });
    return $.codazon.brands;
});