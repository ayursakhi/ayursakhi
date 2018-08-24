(function (factory) {
    if (typeof define === "function" && define.amd) {
        define([
            "jquery","jquery/ui", "cdz_slider",'domReady!','cdz_menu'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
	"use strict";
	
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
	$.fn.sameHeightItems = function(options){
		var defaultConfig = {
			parent: '.same-height',
			sItem: '.product-item-details, .ft-item, .cdz-post',
		};
		var conf = $.extend({},defaultConfig,options);
		var parent = conf.parent;
		var sItem = conf.sItem;
		$(this).each(function(){
			var $wrap = $(this);
			if($wrap.find(parent).length > 0){
				$wrap.find(parent).each(function(){
					var $ul = $(this);
					var setMaxHeight = function(){
						var items = sItem.split(',');
						$(items).each(function(i,el){
							$ul.find(items[i]).css('height','');
							var maxHeight = 0;
							$ul.find(items[i]).each(function(){
								if($(this).height() >= maxHeight){
									maxHeight = $(this).height();	
								}
							});
							$ul.find(items[i]).height(maxHeight);
						});
					};
					setMaxHeight();
					$wrap.off('contentUpdated',setMaxHeight);
					$wrap.on('contentUpdated',
						function(){
							setTimeout(setMaxHeight,500);
						});
					$(window).off('resize.sameHeightItem');
					$(window).on('resize.sameHeightItem',setMaxHeight);
				});
			}
		});
	};
	$.widget('custom.FastedFashion', {
		options: {
		},
		_create: function(){
			var self = this;
			$('.data.item.title').on('click',function(){
				var $title = $(this);
				setTimeout(function(){
					$title.next('.data.item.content').sameHeightItems();
				},500);
			});
			if($('#category-products-grid').length > 0){
				$('#category-products-grid').sameHeightItems();
			}
			if($('.bestseller-product').length > 0){				
				$('.bestseller-product .product-items').each(function(id,el){
					var $ele = $(this);
					self._mobileSlider($ele);
					$(window).resize(function(){
						setTimeout(function(){
							self._mobileSlider($ele);
						},300);
					});
				});
			}
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
			this._alignMenu();
			this._buildMenu();
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
				var threshold = $stickyMenu.height() + $stickyMenu.offset().top;
				var headerHeight = $stickyMenu.height();
				$(window).scroll(function(){
					var $win = $(this);
					var newHeight = 0;
					if($('.sticky-menu.active').length > 0)
						newHeight = $('.sticky-menu.active').height();																			
					var curWinTop = $win.scrollTop() + newHeight;
					if(curWinTop > threshold){
						$stickyMenu.addClass('active');
						$('.panel.wrapper').first().css({'margin-bottom':headerHeight+'px'});
					}else{
    					$('.panel.wrapper').first().css({'margin-bottom':'0px'});
						$stickyMenu.removeClass('active');
					}
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
							0:{items: 	1},
							320:{items:	1},
							360:{items:	2},
							768:{items:	2},
							980:{items:	7},
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
		_alignMenu: function(){                            
			$('.cdz-main-menu > .groupmenu > .level-top > .groupmenu-drop').parent().hover(function() {
				var dropdownMenu = $(this).children('.groupmenu-drop');
				if ($(this).hasClass('parent')) 
					dropdownMenu.css('left', 0);
				var menuContainer = $(this).parents('.header.content').first();
				if(menuContainer.length){
					var left = menuContainer.offset().left + menuContainer.outerWidth() - (dropdownMenu.offset().left + dropdownMenu.outerWidth());
					var leftPos = dropdownMenu.offset().left + left - menuContainer.offset().left;
					if (leftPos < 0) left = left - leftPos;
					if (left < 0) {
						dropdownMenu.css('left', left - 10 + 'px');
					}
				}
			}, function() {
				$(this).children('.groupmenu-drop').css('left', '0px');
			});
		},
		_buildMenu: function(){
			$('.cdz-main-menu > .groupmenu').cdzmenu({
				responsive: true,
				expanded: true,
				delay: 300
			});
		},
		_resize: function () {
			var self = this;					
			$(window).resize(function () {
				if(typeof timeResize != 'undefined'){
			        clearTimeout(timeResize);
			    }
			});					
		}
		
		
	});
	return $.custom.FastedFashion;
}));
