define(['jquery'],function($){
    $.widget('codazon.mobiledropdown',{
        options: {
            adapt: 768,
            item: '.item',
            text: 'Dropdown'
        },
        _create: function(){
            this._initContent();
        },
        _initContent: function(){
            var self = this, config = this.options, adapt = config.adapt;
            self.winwidth = window.innerWidth;
            self.element.addClass('abs-dropdown');
            this.$toggle = $('<a href="javascript:void(0)">').addClass('mobile-toggle visible-xs').text($(config.item,self.element).first().text());
            this.$toggle.insertBefore(self.element);
            function prepare(){
                winwidth = window.innerWidth;
                if(winwidth < adapt){
                    self.element.removeClass('hidden-xs').hide();
                }else{
                    self.element.addClass('hidden-xs').css('display','');
                }
            }
            this.$toggle.click(function(){
                self.element.slideToggle(100,'linear',function(){
                    self.$toggle.toggleClass('open');
                });
            });
            $('body').on('click', function(e) {
                if(self.winwidth < config.adapt) {
                    var $target = $(e.target);
                    var cond1 = $target.is(self.element),
                    cond2 = ($target.parents('.abs-dropdown').length > 0),
                    cond3 = $target.is(self.$toggle);                    
                    if ( !(cond1 | cond2 | cond3) ) {
                        self.element.slideUp(100,'linear',function(){
                            self.$toggle.removeClass('open');
                        });
                    }
                }
            });
            $(config.item,self.element).click(function(){
                var $item = $(this);
                if(self.winwidth < config.adapt){
                    self.element.slideUp(100,'linear',function(){
                        self.$toggle.removeClass('open');
                    });
                }
                self.$toggle.text($item.text());
            });
            $(window).resize(function(){
                var newwidth = window.innerWidth;
                if ( (self.winwidth < adapt && newwidth >= adapt) || (self.winwidth >= adapt && newwidth < adapt) ){
                    prepare();
                }
                self.winwidth = newwidth;
            });
            prepare();
        }
    });
    $.widget('codazon.filterproduct',{
        options: {
            
        },
        _create: function(){
            var self = this;
            $.each(this.options,function(fn,options){
                var namespace = fn.split( "." )[ 0 ];
                var name = fn.split( "." )[ 1 ];
                if (typeof $[namespace] !== 'undefined') {
                    if(typeof $[namespace][name] !== 'undefined') {
                        $[namespace][name](options,self.element);
                    }
                }
            })
        }
    });
    
    return $.codazon.filterproduct;
});