(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery","jquery/ui", "cdz_slider"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    if (typeof $.fn.appearingEffect === 'undefined') {
        $.fn.appearingEffect = function(){
            return this.each(function(){
                var $this = $(this);
                function makeEffect(){
                    $('.cdz-transparent',$this).each(function(i,el){
                        var delay = (i + 1)*300;
                        var $_pItem = $(this);
                        setTimeout(function(){
                            $_pItem.removeClass('cdz-transparent');
                            $_pItem.addClass('cdz-translator');
                        },delay);
                        setTimeout(function(){
                            $_pItem.removeClass('cdz-translator');
                        },delay + 1500);
                    });
                }
                makeEffect();
                $this.on('contentUpdated',makeEffect);
            });
        };
    }
    if (typeof $.fn.initFilter === 'undefined') {
        $.fn.initFilter = function(options){
            var defaultConfig = {
                oneTimeSameHeight: false
            }
            options = $.extend({},defaultConfig, options);
            
            var $_productWrap = $(this), productWrap = $_productWrap.get(0);
            $_productWrap.find(".btn-qty").each(function () {
                var $_btn = $(this);
                var $pr = $_btn.parents('.control').first();
                var $_input = $pr.find('input[type=number]').first();
                var defaultQty = $_input.val();
                if(typeof $_btn.data('bind_qty_event') === 'undefined'){
                    $_btn.data('bind_qty_event',true);
                    $_btn.click(function(e){
                        e.preventDefault();
                        if(!$_input.hasClass('disabled')){
                            var oldValue = parseFloat($_input.val());
                            if(!oldValue){
                                oldValue = 0;
                            }
                            var delta = 1;
                            if($_btn.hasClass('minus')){
                                delta = -1;
                            }
                            var newValue = Math.max(defaultQty, (oldValue + delta) );
                            $_input.val(newValue);
                        }
                    });
                }
            });
            if ( ($_productWrap.find('.same-height').length > 0) || ($_productWrap.find('.cdz-transparent') > 0) ) {
                $_productWrap.sameHeightItems({
                    parent: '.same-height',
                    sItem: '.product-item-details',
                    oneTime: options.oneTimeSameHeight
                });
                function checkVisible(){
                    return productWrap.offsetWidth && productWrap.offsetHeight;
                }
                function sameHeight(){
                    if(!checkVisible()){
                        var interval = setInterval(function(){
                            if(checkVisible()){
                                clearInterval(interval);
                                $_productWrap.trigger('layoutUpdated');
                                $_productWrap.appearingEffect();
                            }
                        },500);
                    } else {
                        $_productWrap.appearingEffect();
                        $_productWrap.trigger('layoutUpdated');
                    }
                }
                sameHeight();
                var winwidth = window.innerWidth;
                $(window).on('resize',function(){
                    if(winwidth != window.innerWidth){
                        sameHeight();
                        winwidth = window.innerWidth;
                    }
                });
            }
        }
    }
    if (typeof $.fn.sameHeightItems === 'undefined') {
        $.fn.sameHeightItems = function(options){
            var defaultConfig = {
                parent: '.same-height',
                sItem: '.product-item-details',
                oneTime: false
            };
            var conf = $.extend({},defaultConfig,options);
            var parent = conf.parent;
            var sItem = conf.sItem;
            $(this).each(function(){
                var $wrap = $(this);
                if(typeof $wrap.data('sameheight') != 'undefined') {
                    var namespaces = $wrap.data('sameheight');
                }else{
                    var namespaces = 'sameheight_' + Math.round(10000*Math.random());
                    $wrap.data('sameheight',namespaces);
                }
                
                if($wrap.find(parent).length > 0){
                    $wrap.find(parent).each(function(){
                        var $ul = $(this);
                        $ul.data('proccessing', false);
                        var setMaxHeight = function(){
                            var items = sItem.trim().split(',');
                            $(items).each(function(i,el){
                                if( $ul.data('proccessing') == false ) {
                                    $ul.data('proccessing', true);
                                    var $li = $ul.find(items[i]);
                                    var maxHeight = 0,
                                    n = $li.length - 1;
                                    $li.each(function(j,el){
                                        $(this).css('height','');
                                        if(j == n) {
                                            $li.each(function(i,el){
                                                var $item = $(this);
                                                var itemHeight = $item.height();
                                                if(itemHeight > maxHeight){
                                                    maxHeight = itemHeight; 
                                                }
                                                if(i == n){
                                                    $li.height(maxHeight);
                                                    if(typeof $ul.data('isotope') !== 'undefined'){
                                                        $ul.data('isotope').arrange();
                                                    }
                                                    $ul.data('proccessing', false);
                                                }
                                            });
                                        }
                                    })
                                }
                            });
                        };
                        setMaxHeight();
                        if( !conf.oneTime ) {
                            $wrap.off('contentUpdated').on('contentUpdated', function(){
                                setTimeout(setMaxHeight,500);
                            }).off('layoutUpdated').on('layoutUpdated', function(){
                                setTimeout(setMaxHeight,100);
                            });
                            $('body').on('contentUpdated', function(){
                                setTimeout(setMaxHeight,500);
                            });
                            var winwidth = window.innerWidth;
                            $(window).off('resize.sameHeightItem.' + namespaces);
                            $(window).on('resize.sameHeightItem.' + namespaces,function() {
                                if( window.innerWidth != winwidth ) {
                                    setMaxHeight();
                                }
                                winwidth = window.innerWidth;
                            });
                        }
                    });
                }
            });
        };
    }
    $.widget('codazon.fastestfashion', {
        _create: function(){
            var self = this;
            $('.data.item.title').on('click',function(){
                var $title = $(this);
                setTimeout(function(){
                    $($title.find('a').attr('href')).sameHeightItems({oneTime: true});
                },300);
            });
            $('.block-products-list').bind('contentUpdated',function(){
                $(this).find('.ajax-item .show-tooltip' ).tooltip({
                    position: {
                      my: "center top-80%",
                      at: "center top",
                      using: function( position, feedback ) {
                        $( this ).css( position );
                        $(this).addClass("cdz-tooltip");
                      }
                    }
                        });
                $(this).find('.ajax-item').removeClass('ajax-item');
            });
            this._backTopButton();
            this._scrollNext();
            if(ThemeOptions.sticky_header){
                this._stickyMenu();
            }
        },      
        _scrollNext: function() {
            $('[data-role="scroll_next"]').click(function() {
                var $element = $(this), effectClass = 'animated fadeIn',
                elTop = $element.offset().top;
                $('body').addClass(effectClass);
                $('body,html').animate({
                    scrollTop: elTop,
                }, 500, function(){
                    $('body').removeClass(effectClass);
                });
            });
        },
        _backTopButton: function(){
            var $backTop = $('#back-top');
            if($backTop.length){
                $backTop.hide();
                $(window).scroll(function() {
                    if ($(this).scrollTop() > 100) {
                        $backTop.fadeIn();
                    } else {
                        $backTop.fadeOut();
                    }
                });
                $('a', $backTop).click(function() {
                    $('body,html').animate({
                        scrollTop: 0
                    }, 800);
                    return false;
                });
            }
        },
        _stickyMenu: function(){
            var $stickyMenu = $('.sticky-menu').first();
            if( $stickyMenu.length > 0 ){
                var threshold = 300;
                var $parent = $stickyMenu.parent();
                var $win = $(window);
                var t = false, w = $win.prop('innerWidth');
                
                $parent.css({minHeight:''});
                var parentHeight = $parent.height();
                $parent.css({minHeight:parentHeight});
                
                $win.scroll(function(){
                    if ($win.scrollTop() > threshold) {
                        $stickyMenu.addClass('active');
                    } else {
                        $stickyMenu.removeClass('active');
                    }
                });
                $win.on('resize',function () {
                    if (t) {
                        clearTimeout(t);
                    }
                    t = setTimeout(function () {
                        var newWidth = $win.prop('innerWidth');
                        if (w != newWidth) {
                            $stickyMenu.removeClass('active');
                            $parent.css({minHeight:''});
                            parentHeight = $parent.height();
                            $parent.css({minHeight: parentHeight});
                            w = newWidth;
                        }
                    },50);
                });
            }
        }
    });
    return $.codazon.fastestfashion;
}));
