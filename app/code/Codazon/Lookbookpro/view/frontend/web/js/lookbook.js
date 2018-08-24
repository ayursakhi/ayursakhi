define([
    'jquery',
    'jquery/ui',
    'owlslider'
], function($){
    $.widget('codazon.lookbook', {
        options: {
            items:              '[data-role=item]',
            itemModal:          '[data-role=item-modal]',
            smallItemPoints:    '[data-role=small-item-points]',
            largeItemPoints:    '[data-role=large-item-points]',
            productHtmlData:    '[data-role=product-html-data]',
            modalContainer:     '[data-role=modal-container]',
            modalClose:         '[data-role=close-modal]',
            modalInner:         '[data-role=modal-inner]',
            modalContent:       '[data-role=modal-content]',
            modalSlider:        '[data-role=modal-slider]',
            productSlider:      '[data-role=product-slider]'     
        },
        _create: function() {
            this._assignVariables();
            this._prepareHtml();
            this._bindEvents();
        },
        _assignVariables: function() {
            var self = this, conf = this.options;
            this.$items = $(conf.items, this.element);
            this.$itemModal = $(conf.itemModal, this.element);
            this.$productHtmlData = $(conf.productHtmlData, this.element);
            this.$modalContainer = $(conf.modalContainer, this.element);
            this.$modalSlider = $(conf.modalSlider, this.element);
            this.$modalContainer.addClass('first-load');
            this.rtl = $('body').hasClass('rtl-layout');
        },
        _prepareHtml: function() {
            var self = this, conf = this.options;
            this.$items.each(function(id, el) {
                var $item = $(this);
                $item.attr('data-itemid', id);
                var $modalItem = $(conf.itemModal, $item).attr('data-modalid', id);
                if (!$modalItem.find(conf.largeItemPoints).html()) {
                    var html = $item.find(conf.smallItemPoints).html();
                    $modalItem.find(conf.largeItemPoints).html(html);
                }
                $modalItem.appendTo(self.$modalSlider).css('display', '');
                var $productSlider = $(conf.productSlider, $modalItem);
                var products = $productSlider.data('productid');
                
                if (products) {
                    products = products.toString().split(',');
                    $.each(products, function(id, productId) {
                        var $product = self.$productHtmlData.find('[data-productid=' + productId + ']');
                        if ($product.length) {
                            $product.clone().appendTo($productSlider).attr('data-slideid', id);
                        }
                    });
                    $productSlider.addClass('owl-carousel').owlCarousel({
                        responsiveClass: true,
                        responsive: {
                            1900: {items: 3},
                            1600: {items: 2},
                            768:  {items: 2},
                            480:  {items: 2},
                            0:    {items: 2}
                        },
                        responsiveRefreshRate: 500,
                        nav: true,
                        dots: true,
                        margin: 20,
                        lazyLoad: true,
                        navElement: 'div',
                        rtl: self.rtl
                    });
                }
                
            });
            this.$modalSlider.addClass('owl-carousel');
            this.$modalSlider.owlCarousel({
                items: 1,
                nav: true,
                dots: false,
                lazyLoad: true,
                mouseDrag: false,
                touchDrag: false,
                navElement: 'div',
                rtl: self.rtl
            });
            this.sliderData = this.$modalSlider.data('owl.carousel');
            window.sliderData = this.sliderData;
        },
        _bindEvents: function() {
            var self = this, conf = this.options;
            this.$items.each(function(id, el) {
                var $item = $(this);
                $item.on('click', function() {
                    var itemId = $item.data('itemid');                    
                    self.$modalContainer.removeClass('_hide fadeOut').addClass('animated fadeIn');
                    $('body').addClass('lb-modal-open');
                    self.sliderData.to(itemId, 0, true);
                    if (self.$modalContainer.hasClass('first-load')) {
                        setTimeout(function() {
                            self.$modalContainer.removeClass('first-load');
                        }, 400);
                    }
                });
            });
            this.$modalContainer.on('click', conf.modalClose, function() {
                self.$modalContainer.removeClass('animated fadeIn').addClass('animated fadeOut');
                $('body').removeClass('lb-modal-open');
                setTimeout(function() {
                    self.$modalContainer.addClass('_hide');
                }, 500);
            });
            $(conf.itemModal, this.element).each(function() {
                var $modalItem = $(this);
                var $productSlider = $(conf.productSlider, $modalItem);
                $modalItem.find('.item-point').each(function() {
                    var $point = $(this);
                    var productId = $point.data('productid'), $product = $('[data-productid=' + productId + ']', $productSlider),
                    slideId = $product.data('slideid');
                    $point.attr('href', $product.data('href'))
                    $product.find('[data-role=product-number]').text($point.text());
                    var onMouseIn = function() {
                        if (!$product.parents('.owl-item').first().hasClass('active')) {
                            $productSlider.data('owl.carousel').to(slideId, 0, true);
                        }
                        $productSlider.find('[data-productid]').addClass('product-hide');
                        $product.addClass('product-active').removeClass('product-hide');
                        $point.addClass('point-active').siblings().addClass('point-disabled');
                    };
                    var onMouseOut = function() {
                        $product.removeClass('product-active')
                        $productSlider.find('.product-hide').removeClass('product-hide');
                        $point.removeClass('point-active').siblings().removeClass('point-disabled');
                    };
                    $point.hover(onMouseIn, onMouseOut);
                    $product.hover(onMouseIn, onMouseOut);
                });
            });
        }
        
    });
    return $.codazon.lookbook;
    
});