define(['jquery','owlslider'], function($) {
    $.widget('codazon.imggallery', {
        options: {
            parent: '.product-item',
            mainImg: '.main-image img:first',
            itemCount: 5,
            activeClass: 'item-active',
            loadingClass: 'swatch-option-loading',
        },
        _create: function(){
            var self = this, config = this.options;
            if(config.images.length == 0) {
                return false;
            }
            this.$parent = this.element.parents(config.parent).first();
            this.$mainImg = $(config.mainImg,this.$parent);
            this.images = config.images;
            this.initHtml();
            this.bindHoverEvent();
            this.element.css({minHeight:''});
        },
        initHtml: function(){
            var self = this, config = this.options;
            this.$slider = $(this.getHtml(this.images));
            this.$slider.appendTo(this.element);
        },
        bindHoverEvent: function(){
            var self = this, config = this.options;
            $('.gitem',this.$slider).each(function(){
                var $gitem = $(this), $link = $('.img-link',$gitem), $img = $('img',$link);
                var mainSrc = $link.attr('href');
                $link.on('click',function(e){
                    e.preventDefault();
                }).hover(
                    function(){
                        $gitem.addClass(config.activeClass).siblings().removeClass(config.activeClass);
                        if(typeof $link.data('loaded') === 'undefined') {
                            var mainImg = new Image();
                            self.$mainImg.addClass(config.loadingClass);
                            $(mainImg).load(function(){
                                self.$mainImg.removeClass(config.loadingClass);
                                self.$mainImg.attr('src',mainSrc);
                                $link.data('loaded',true);
                            });
                            mainImg.src = mainSrc;
                        }else{
                            self.$mainImg.attr('src',mainSrc);
                        }
                    }
                );
            });
        },
        getHtml: function(images){
            var self = this, config = this.options;
            var html =  '<div class="gitems">';
            $.each(images,function(id,img){
                html += '<div class="gitem">';
                html +=     '<a class="img-link" href="'+ img.large +'"><img class="img-responsive" src="'+ img.small +'" /></a>';
                html += '</div>';
            });
            html += '</div>';
            return html;
        }
    });
    return $.codazon.imggallery;
});