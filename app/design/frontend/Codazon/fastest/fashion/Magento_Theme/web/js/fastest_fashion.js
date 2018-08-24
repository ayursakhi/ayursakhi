(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery","jquery/ui", "cdz_slider",'domReady!'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    
    $.widget('codazon.fastestfashion', {
        _create: function(){
            var self = this;
            function makeMobileSlider(){
                if($('.bestseller-product').length > 0){            
                    $('.bestseller-product .product-items').each(function(id,el){
                        var $ele = $(this);
                        if(typeof $ele.data('mbslider') === 'undefined') {
                            $ele.data('mbslider',true);
                            self._mobileSlider($ele);
                            $(window).resize(function(){
                                setTimeout(function(){
                                    self._mobileSlider($ele);
                                },300);
                            });
                        }
                    });
                }
            }
            makeMobileSlider();
            $('body').on('contentUpdated', function() {
                makeMobileSlider();
                self._sameHeightItems();
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
            if(ThemeOptions.sticky_header){
                this._stickyMenu();
            }
            this._sameHeightItems();  
            this._resize();
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
        },
        
        _mobileSlider: function($container){
            if ($container) {
                var wWidth = $(window).width();
                if(wWidth <= 767){
                    $container.addClass('owl-carousel');
                    $container.owlCarousel({
                        loop: true,
                        margin: 20,
                        responsiveClass: true,
                        nav: true,
                        dots: false,
                        rtl: ThemeOptions.rtl_layout == 1 ? true : false,
                        responsive:{
                            0:{items:   1},
                            320:{items: 1},
                            360:{items: 2},
                            768:{items: 2},
                            980:{items: 7},
                            1200:{items: 7}
                        }   
                    });
                }else{
                    if($container.hasClass('owl-carousel')){
                        $container.data('owl.carousel').destroy();
                        $container.removeClass('owl-carousel owl-loaded');
                        $container.find('.owl-stage-outer').children().unwrap();
                        $container.removeData();
                    }
                }
            }
        },
        _checkVisible: function($el) {
            return $el.get(0).offsetWidth && $el.get(0).offsetHeight && $el.is(':visible');
        },
        _sameHeightItems: function(){
            var self = this;
            if($('.same-height').length > 0){
                $('.same-height').each(function() {
                    var $ul = $(this);
                    var makeSameHeight = function() {
                        var maxHeight = 0;
                        $ul.find('.product-item-details').css('height', '');
                        n = $ul.find('.product-item-details').length - 1;
                        $ul.find('.product-item-details').each(function(i, el) {                                                                             
                            if($(this).height() > maxHeight) {
                                maxHeight = $(this).height();
                            }
                            if(i == n) {
                                $ul.find('.product-item-details').height(maxHeight);
                            }
                        });
                    }
                    var interval = false;
                    if(self._checkVisible($ul)){
                        makeSameHeight();
                    }else{
                        interval = setInterval(function() {
                            if (self._checkVisible($ul)) {
                                clearInterval(interval);
                                makeSameHeight();
                            }
                        }, 500);
                    }
                });
            }
        }, 
        _resize: function () {
            var self = this;      
            $(window).resize(function () {
                if(typeof timeResize != 'undefined'){
                    clearTimeout(timeResize);
                }
                var timeResize = setTimeout(function(){                 
                    self._sameHeightItems();
                },250); 
            });
        }
    });
    return $.codazon.fastestfashion;
}));