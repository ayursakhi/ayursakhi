define(['jquery','Codazon_ProductFilter/js/lightslider.min'], function($) {
    $.widget('codazon.verticalgallery', {
        options: {
            parent: '.product-item-info',
            mainImg: '.product-item-photo .product-image-photo:first',
            item: 4,
            slideMargin: 8,
            adapt: 768,
            smallImgType: 'small_default',
            largeImgType: 'home_default'
        },
        _isElVisible: function (el) {
            var pr = $(el).parent().get(0);
            return pr.offsetWidth > 0 && pr.offsetHeight > 0;
        },
        _create: function () {
            var self = this;
            var visibleInterval = false;
            self.element.removeAttr('data-vslider');
            setTimeout(function () {
                function checkVisible()
                {
                    if (self._isElVisible(self.element.get(0))) {
                        setTimeout(function () {
                            window.clearInterval(visibleInterval);
                            self._initHtml();
                        },300);
                    }
                }
                if (self._isElVisible(self.element.get(0))) {
                    self._initHtml();
                } else {
                    visibleInterval = window.setInterval(checkVisible, 500);
                }
            },500);
        },
        _initHtml: function () {
            var self = this, config = self.options;
            self.$parent = this.element.parents(config.parent).first();
            self.$mainImg = self.$parent.find(config.mainImg).first();
            
            var img = new Image();
            img.src = self.$mainImg.attr('src');
            $(img).load(function () {
                self.element.css({display:'block', opacity:0, position:'absolute'});
                self.height = self.$parent.height() - parseInt(self.element.css('padding-top')) - parseInt(self.element.css('padding-bottom'));
                self.width = config.width;
                self._createSlider(config);
                setTimeout(function () {
                    self.element.css({display:'',opacity:'', position:''});
                },500);
                function refresh()
                {
                    var visibleInterval = false;
                    function calcSliderSize()
                    {
                        if (typeof self.slider.setConfig === 'function') {
                            self.element.css({display:'block', opacity:0, position:'absolute'});
                            self.height = self.$parent.height() - parseInt(self.element.css('padding-top')) - parseInt(self.element.css('padding-bottom'));
                            var imgHeight = self.$firstImg.height() + 2; /*2 borders width*/
                            config.item = Math.floor(self.height/imgHeight);
                            config.item = Math.ceil(config.item - (config.item*config.slideMargin/imgHeight));
                            self.slider.setConfig({'verticalHeight': self.height, item: config.item});
                            self.slider.refresh();
                            setTimeout(function () {
                                self.element.css({display:'', opacity:'', position:''});
                            },200);
                        }
                    }
                    function checkVisible()
                    {
                        if (self._isElVisible(self.element.get(0))) {
                            setTimeout(function () {
                                window.clearInterval(visibleInterval);
                                calcSliderSize();
                            },300);
                        }
                    }
                    if (self._isElVisible(self.element.get(0))) {
                        calcSliderSize();
                    } else {
                        visibleInterval = window.setInterval(checkVisible, 500);
                    }
                }
                $(window).on('grid_mode list_mode toggleLeftColumnCompleted',function () {
                    setTimeout(function () {
                        refresh();
                    },400);
                });
                if (self.element.parents('[data-sameheight]').length) {
                    var $_sameHeight = self.element.parents('[data-sameheight]').first();
                    $_sameHeight.on('sameheight_completed',function () {
                        setTimeout(function () {
                            refresh();
                        },300);
                    });
                }
                var $win = $(window);
                var curWinWidth = $win.prop('innerWidth');
                $win.on('resize',function () {
                    var newWinWidth = $win.prop('innerWidth');
                    if ( (newWinWidth >= config.adapt) && (newWinWidth != curWinWidth) ) {
                        setTimeout(function () {
                            refresh();
                        },400);
                    }
                    curWinWidth = newWinWidth;
                });
            });
        },
        _createSlider: function () {
            var self = this, config = this.options;
            var $loader = $('<span class="vImgLoader"><span>Loading</span></span>');
            var $imgParent = self.$mainImg.parent();
            $loader.css({position: 'absolute',
                width: '100%', height: '100%', backgroundColor: '',
                zIndex: 10000, top: 0, left:0, textAlign: 'center', paddingTop: '50%',
                display: 'none'
            });
            $loader.appendTo($imgParent);
            
            var html = '<ul class="img-slider">';
            $(config.images).each(function (i,img) {
                html += '<li class="item"><a href="'+img.large+'"><img class="img-responsive" src="'+img.small+'" /></a></li>'
            });
            html += '</ul>';
            self.slider = $(html);
            self.slider.appendTo(self.element);
            self.$firstImg = self.slider.find('img').first();
            var firstImg = new Image();
            firstImg.src = config.images[0].small;
            $(firstImg).load(function () {
                self.$mainImg.data('processing',false);
                self.slider.find('a').each(function () {
                    var $a = $(this), loaded = false;
                    $a.click(function (e) {
                        e.preventDefault();
                    });
                    $a.hover(function (e) {
                        e.preventDefault();
                        if (!self.$mainImg.data('processing')) {
                            self.$mainImg.data('processing',true);
                            var src = $(this).attr('href');
                            self.slider.find('a').parent().removeClass('img-active');
                            $a.parent().addClass('img-active');
                            if (!loaded) {
                                var mainImage = new Image();
                                mainImage.src = src;
                                $loader.show();
                                $(mainImage).load(function () {
                                    $loader.hide();
                                    loaded = true;
                                    self.$mainImg.attr('src',src);
                                    self.$mainImg.data('processing',false);
                                });
                            } else {
                                self.$mainImg.attr('src',src);
                                self.$mainImg.data('processing',false);
                            }
                        }
                    }, function (){});
                });
                var imgHeight = self.$firstImg.height();
                config.item = Math.floor(self.height/imgHeight);
                config.item = Math.ceil(config.item - (config.item*config.slideMargin/imgHeight));
                self.slider.lightSlider(self._getSliderSettings(config));
                $('.lSAction',self.element).insertBefore($('.lSSlideWrapper',self.element));
            });
        },
        _getSliderSettings: function (config) {
            var self = this;
            return {
                item: config.item,
                vertical: true,
                verticalHeight: self.height,
                pager: false,
                slideMargin: config.slideMargin
            }
        }
    });
    return $.codazon.verticalgallery;
});