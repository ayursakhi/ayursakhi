/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "Codazon_AjaxLayeredNav/js/isotope.pkgd.min",
    "jquery/ui",
    "matchMedia",
    'Codazon_AjaxLayeredNav/js/layout',
    'Codazon_ProductFilter/js/product-gallery'
],function($,Isotope) {
    if(typeof ThemeOptions == 'undefined') {
        ThemeOptions.rtl_layout = false;
    }
    $.widget('codazon.categoryLayout',{
        options: {
            namespace: 'categoryLayout',
            sidebarBtn: '[data-role="toggle-sidebar"]',
            gridBtn: '.mode-grid',
            listBtn: '.mode-list',
            sidebar: '.sidebar-container',
            mainCol: '.column.main',
            productList: '.products',
            fullClass: 'full-width',
            activeClass: 'active',
            mainColWidth: '75%',
            absLeft: 500,
            layoutListing: {'1':{1200: 'desktop_5', 768: 'tablet_4', 0: 'mobile_2' },'2':{1200: 'desktop_4', 768: 'tablet_3', 0: 'mobile_2' }},
            layoutListMode: 'desk_1',
            layoutContainer: '#category-products-grid',
            direction: 'left',
            delay: 200,
            sidebarOff: 'sidebar-off',
            originLeft: (!ThemeOptions.rtl_layout)?true:false,
            isoWrap: '#category-products-grid .product-items',
            isoItem: '.product-item',
            toolbar: '#toolbar-wrap',
            mbAdapt: 768,
            listClass: 'list products-list',
            gridClass: 'grid products-grid'
        },
        _create: function(){
            this._assignElements();
            this._prepareHtml();
            this._bindEvent();
            this._isoLayout();
        },
        _assignElements: function(){
            var self = this, config = this.options;
            this.$layout = $(config.layoutContainer).first();
            this.$sidebar = $(config.sidebar).first();
            this.$mainCol = $(config.mainCol).first();
            this.$parent = this.$sidebar.parent();
            this.$productList = $(config.productList, this.$mainCol).first();
            this.$toolbar = $(config.toolbar).first();
            this.$pcToolbar = $('<div class="pc-toolbar">');
            this.$pcToolbar.insertBefore(this.$toolbar);
            this.sidebarOff = config.sidebarOff;
            this.$sidebarBtn = $(config.sidebarBtn);
            this.$isoWrap = $(config.isoWrap);
            this.$isoItem = $(config.isoItem);
            this.$gridBtn = $(config.gridBtn);
            this.$listBtn = $(config.listBtn);
            if(typeof ThemeOptions.allGridClass != 'undefined') {
                config.layoutListing = ThemeOptions.allGridClass;
            }
            var isMobile = function() {
              var check = false;
              (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
              return check;
            };
            this.isMobile = isMobile();
        },
        _prepareHtml: function() {
            var self = this, config = this.options;
            var winwidth = window.innerWidth;
            self.$toolbar.css({display:''});
            this._adaptLayout = function(winwidth) {
                if (winwidth < config.mbAdapt) {
                    self.$toolbar.appendTo(self.$pcToolbar);
                    self.$sidebarBtn.hide();
                    self._showSideBar(false);
                    self.$sidebar.css({position: ''});
                } else {
                    self.$toolbar.insertBefore(self.$parent);
                    self.$sidebarBtn.css('display','');
                    self._updateSidebar();
                }
            }
            this._adaptLayout(winwidth);
            if(this._isListMode()){
                this._switchToList();
            }
            if( this.$isoWrap.find('.img-gallery').length ){
                try{
                    this.$isoWrap.find('.img-gallery').each(function() {
                        var $gallery = $(this);
                        if( $gallery.attr('data-mage-init') ){
                            var galleryData = $gallery.data('mage-init')['Codazon_ProductFilter\/js\/product-gallery'];
                            $.codazon.imggallery(galleryData,$gallery);
                            $gallery.removeAttr('data-mage-init');
                        } 
                    });
                }catch(e){
                    
                }
            }
        },
        _isoLayout: function(){
            var self = this, config = this.options;
            if(this.isMobile) {
                var duration = 0;
            } else {
                var duration = 300;
            }
            setTimeout(function() {
                self._refreshItemHeight();
                self.iso = new Isotope( config.isoWrap, {
                    transitionDuration: duration,
                    itemSelector: config.isoItem,
                    originLeft: config.originLeft
                });
                self.$isoWrap.data('isotope',self.iso);
            }, 500);
            
            var winwidth = window.innerWidth;
            var isoTimeout = false;
            $(window).resize(function(){
                if(winwidth != window.innerWidth) {
                    if (isoTimeout) {
                        clearTimeout(isoTimeout);
                    }
                    isoTimeout = setTimeout(function() {
                        self._refreshItemHeight();
                    }, 100);
                    winwidth = window.innerWidth;
                }
            });
        },
        _bindEvent: function(){
            var self = this, config = this.options,
            click = 'click.' + config.namespace,
            resize = 'resize.' + config.namespace;
            changeAdapt = 'changeAdapt' + config.namespace;
            if(!this._isOneColumnPage()){
                this.$sidebarBtn.off(click).on(click,function(e){
                    e.preventDefault();
                    self._toggleSideBar();
                });
            }else{
                this.$sidebarBtn.hide();
            }
            this.$listBtn.off(click).on(click,function(e){
                e.preventDefault();
                self._switchToList();
            });
            this.$gridBtn.off(click).on(click,function(e){
                e.preventDefault();
                self._switchToGrid();
            });
            var winwidth = window.innerWidth;
            $(window).off(resize).on(resize,function(){
                var newwidth = window.innerWidth;
                if( (winwidth < config.mbAdapt && newwidth >= config.mbAdapt) || (winwidth >= config.mbAdapt) && (newwidth < config.mbAdapt) ){
                    $(window).trigger(changeAdapt,[newwidth]);
                }
                winwidth = newwidth;
            });
            $(window).off(changeAdapt).on(changeAdapt,function(e,newwidth){
                self._adaptLayout(newwidth);
            });
        },
        _switchToList: function() {
            var self = this, config = this.options;
            this.$listBtn.addClass(config.activeClass);
            this.$gridBtn.removeClass(config.activeClass);
            this.$productList.removeClass(config.gridClass).addClass(config.listClass);
            this.$layout.removeAttr('class').addClass(config.layoutListMode);
            this.$isoItem.each(function() {
                var $item = $(this);
                var $el, $dest;
                if( $('.img-gallery',$item).length ) {
                    $el = $('.img-gallery',$item);
                    $dest = $('.cdz-hover-section',$item);
                    $el.appendTo($dest);
                }
                if($('.qs-button',$item).length) {
                    $el = $('.qs-button',$item);
                    $dest = $('[data-role="add-to-links"]',$item);
                    $el.prependTo($dest);
                }
            });
            this._refreshItemHeight();
            if(typeof self.iso !== 'undefined') {
                self.iso.arrange();
            }
            $('body').addClass('js_list');
        },
        _switchToGrid: function() {
            $('body').removeClass('js_list');
            var self = this, config = this.options;
            this.$gridBtn.addClass(config.activeClass);
            this.$listBtn.removeClass(config.activeClass);
            this.$productList.removeClass(config.listClass).addClass(config.gridClass);
            this.$layout.removeAttr('class');
            if(!this._isOneColumnPage()){
                if(this._isSidebarHidden()){
                    this._switchLayout(config,'2','1');
                }else{
                    this._switchLayout(config,'1','2');
                }
            }else{
                this._switchLayout(config,'2','1');
            }
            this.$isoItem.each(function() {
                var $item = $(this);
                var $el, $dest;
                if( $('.img-gallery',$item).length ) {
                    $el = $('.img-gallery',$item);
                    $dest = $('.product-item-details',$item);
                    $el.prependTo($dest);
                }
                if( $('.qs-button',$item).length ) {
                    $el = $('.qs-button',$item);
                    $dest = $('.addto-button',$item);
                    $el.appendTo($dest);
                }
            });
            this._refreshItemHeight();
            if(typeof self.iso !== 'undefined') {
                self.iso.arrange();
            }
        },
        _refreshItemHeight: function(){
            if(typeof $.fn.sameHeightItems != 'undefined') {
                this.$layout.sameHeightItems({oneTime: true});
            }
        },
        _updateSidebar: function() {
            if (this._isSidebarHidden()) {
                this._hideSidebar(false);
            } else {
                this._showSideBar(false);
            }
        },
        _toggleSideBar: function(){
            if (this._isSidebarHidden()) {
                this._showSideBar(true);
            } else {
                this._hideSidebar(true);
            }
        },
        _showSideBar: function(effect) {
            var self = this, config = this.options,
            parentHeight = this.$parent.outerHeight();
            this._switchLayout(config,'1','2');
            self.$sidebar.show();
            var before = {opacity: 0, position: 'absolute', top: 0};
            var after = {opacity: 1};
            if (config.originLeft) {
                before.left = -config.absLeft;
                after.left = 0;
            } else {
                before.right = -config.absLeft;
                after.right = 0;
            }
            
            if (effect) {
                this.$sidebarBtn.attr('disabled','disabled');
                this.$parent.css({height: parentHeight});
                this.$sidebar.css(before);
                this.$sidebar.animate(after,config.delay,'linear',function(){
                    self.$parent.css({height: ''});
                    $('body').removeClass(self.sidebarOff);
                    self.$sidebarBtn.removeAttr('disabled');
                });
                this.$mainCol.animate({
                    width: config.mainColWidth
                },config.delay,'linear', function(){
                    self.$sidebar.css({position: ''});
                    self.$mainCol.css({width: ''});
                    self._refreshItemHeight();
                    self.iso.arrange();
                });
            } else {
                $('body').removeClass(self.sidebarOff);
                self.$mainCol.css({width: ''});
                self.$sidebar.css(after);
                self._refreshItemHeight();
                if(typeof self.iso !== 'undefined') {
                    self.iso.arrange();
                }
            }
        },
        _hideSidebar: function(effect) {
            var self = this, config = this.options,
            parentHeight = this.$parent.outerHeight();
            this._switchLayout(config,'2','1');
            var before = {opacity: 1, position: 'absolute', top: 0};
            var after = {opacity: 0};
            if (config.originLeft) {
                before.left = 0;
                after.left = -config.absLeft;
            } else {
                before.right = 0;
                after.right = -config.absLeft;
            }
            if (effect) {
                this.$sidebarBtn.attr('disabled','disabled');
                this.$parent.css({height: parentHeight});
                this.$sidebar.css(before);
                this.$sidebar.animate(after,config.delay,'linear',function(){
                    self.$sidebar.hide();
                    self.$parent.css({height: ''});
                    $('body').addClass(self.sidebarOff);
                    self.$sidebarBtn.removeAttr('disabled');
                });
                this.$mainCol.animate({
                    width: '100%'
                },config.delay,'linear',function() {
                    self._refreshItemHeight();
                    self.iso.arrange();
                });
            } else {
                $('body').addClass(self.sidebarOff);
                self.$sidebar.css(after);
                self.$mainCol.css({width:'100%'});
                self._refreshItemHeight();
                if(typeof self.iso !== 'undefined') {
                    self.iso.arrange();
                }
            }
        },
         _switchLayout: function (config,from,to) {
            ThemeOptions.layoutGridClass = config.layoutListing[to];
            var winwidth = window.innerWidth;
            for (var key in config.layoutListing[from]) {
                this.$layout.removeClass(config.layoutListing[from][key]);
            }
            
            if(this._isListMode()){
                this.$layout.addClass(config.layoutListMode);
            } else {
                var adapt = 0;
                for (var key in config.layoutListing[to]) {
                    if(winwidth > key){
                        adapt = key;
                    }
                }
                this.$layout.addClass(config.layoutListing[to][adapt]);
            }
        },
        _isListMode: function() {
            return this.$productList.hasClass(this.options.listClass) | $('body').hasClass('js_list');
        },
        _isSidebarHidden: function(){
            return ($('body').hasClass(this.sidebarOff) | this._isOneColumnPage());
        },
        _isOneColumnPage: function(){
            return $('body').hasClass('page-layout-1column');
        }
    });
    return $.codazon.categoryLayout;
})