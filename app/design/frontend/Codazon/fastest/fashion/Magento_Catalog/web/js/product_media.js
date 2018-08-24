define([
	'jquery',
	'royalslider'
],function($){
	var curWinWidth = $(window).width();
	var adapt = 768;
	function changeAdapt(){
		$(window).resize(function(){
			setTimeout(function(){
				var winWidth = $(window).width();
				if( (curWinWidth < adapt) && (winWidth >= adapt) ){
					$(window).trigger('cdz_pc');				
				}else if( (curWinWidth >= adapt) && (winWidth < adapt)){
					$(window).trigger('cdz_mobile');
				}
				curWinWidth = winWidth;
			},200);
		});
	}
	changeAdapt();
	$.fn.cdzZoom = function(options){
		var defaultConfig = {
			mainImg: '.rsMainSlideImage',
			magnify: '.magnify'
		};
		options = $.extend({},defaultConfig,options);
		$(this).each(function(index, element) {
			var $this = $(this);
			var $magnify = $this.find(options.magnify);
			var $mainImg = $this.find(options.mainImg);
			var nativeWidth = 0;
			var nativeHeight = 0;
			$this.data('cdzZoom',true);
			$this.on('mousemove.cdzZoom',
				function(e){
					if(!nativeWidth && !nativeHeight){
						var imgObject = new Image();
						imgObject.src = $mainImg.attr('src');
						nativeWidth = imgObject.width;
						nativeHeight = imgObject.height;
					}else{
						var magnifyOffset = $this.offset();
						var mx = e.pageX - magnifyOffset.left;
						var my = e.pageY - magnifyOffset.top;
					}
					if(mx < $this.width() && my < $this.height() && mx > 0 && my > 0){
						$magnify.fadeIn(100);
					}
					else{
						$magnify.fadeOut(100);
					}
					if($magnify.is(':visible')){
						var rx = Math.round(mx/$mainImg.width()*nativeWidth - $magnify.width()/2)*(-1);
						var ry = Math.round(my/$mainImg.height()*nativeHeight - $magnify.height()/2)*(-1);
						var bgp = rx + "px " + ry + "px";
						var px = mx - $magnify.width()/2;
						var py = my - $magnify.height()/2;
						$magnify.css({left: px, top: py, backgroundPosition: bgp});
					}
				}
			);
			$this.on('mouseleave.cdzZoom',function(e){
				$magnify.fadeOut(100);
			}); 
		}); 
	};
	$.fn.productMedia = function(options){
		var defaultConf = {
			media: false,
			thumbStyle: 'vertical',
			uniqid: ''
		};
		var conf = $.extend({},defaultConf,options);
		return this.each(function(){
			var $element = $(this);
			var productMediaSetup = {
				_create: function () {				
					this._initMedia();
				},
				_initMedia: function(){
					var self = this;
					this._loadMediaHtml( conf.media );
					this._setUpMediaSlider( $element ,conf.thumbStyle);
					this._updateSwatchImages();
				},
				_loadMediaHtml: function(media){
					var self = this;
					var mainHtml = '', moreviewHtml = '', html = '';
					$(media).each(function(id,el){
						if(el.isMain){
							mainHtml += self._getSlideItemHtml(el); 
						}else{
							moreviewHtml +=  self._getSlideItemHtml(el);	
						}
					});
					html = mainHtml + moreviewHtml;
					$element.html(html);
				},
				_getSlideItemHtml: function(el){
					var html = '';
					html +=	'<div class="rsContent">';
					html +=		'<a class="rsImg"  href="'+el.img+'" data-rsBigImg="'+el.full+'">';
					html +=			'<img class="rsTmb" src="'+el.thumb+'" data-rsmainimg="'+el.img+'" />';
					html +=		'</a>';
					html +=		'<div class="magnify" style="background: url(\''+el.img+'\') no-repeat; width:225px; height:225px;"></div>'
					html +=	'</div>';
					return html;
				},
				_setUpMediaSlider: function(slideHander,thumbStyle){
					var $slider = $(slideHander);
					var totalSlides = $slider.find('.rsImg').length;
					var timeoutId = false;
					var uniqid = conf.uniqid;
					$slider.royalSlider({
						fullscreen: {
						  enabled: true,
						  nativeFS: false,
						},
						deeplinking: {
							enabled: true
						},
						controlNavigation: 'thumbnails',
						thumbs: {
						  orientation: thumbStyle,
						  paddingBottom: 0,
						  firstMargin: false,
						  appendSpan: true,
						  autoCenter : false,
						  spacing: 10,
						},
						
						imageScaleMode: 'none',
						imageAlignCenter: false,
						autoScaleSlider: false,
						usePreloader: true,
						numImagesToPreload: 100,
						transitionType:'move',
						imageScalePadding: 0,
						autoHeight: false,
						loop: true,
						arrowsNav: true,
						margin: '0px auto',
						keyboardNavEnabled: true,
						addActiveClass: true
					});
					var $curMainSlide = $slider.find('.rsSlide').first();
					$curMainSlide.addClass('product-image');		
					var slider =  $slider.data('royalSlider');
					var timeout = false;
					var curId;
					var setZoomForActiveSlide = function($rsActiveSlide){
						if(typeof $rsActiveSlide === 'undefined'){
							$rsActiveSlide = $curMainSlide;
						}
						var $rsInner = $rsActiveSlide.find('.rs-inner');
						if($rsInner.length == 0){
							var $rsInner = $('<div class="rs-inner"></div>');
							$('.rsContent',$rsActiveSlide).append($rsInner);
							$('.rsMainSlideImage',$rsActiveSlide).appendTo($rsInner);
							$('.magnify',$rsActiveSlide).appendTo($rsInner);
						}
						if( (!$rsInner.data('cdzZoom')) ||  ($rsActiveSlide.data('tempImage'))){
							$rsInner.off('mousemove.cdzZoom');
							$rsInner.off('mouseleave.cdzZoom');
							$rsInner.cdzZoom();
							$rsActiveSlide.data('tempImage',false);
						}
					}
					var zoomImage = function(){
						$slider.find('.rsSlide .rsContent').each(function(index, element) {
							var $pr = $(this).parent();
							setZoomForActiveSlide($pr);
						});	
					}
					$.fn.returnImageSrc = function(){
						$(this).each(function(){
							var $this = $(this);
							$this.click(function(){
								if(!$slider.hasClass('rsFullscreen')){
									var src = $this.find('.rsNavSelected img').data('rsmainimg');
									var $rsActiveSlide = $slider.find('.rsActiveSlide');
									$('.rsMainSlideImage',$rsActiveSlide).attr('src',src);
									$('.magnify',$rsActiveSlide).css({background: 'url('+src+') no-repeat'});
									if($curMainSlide.data('tempImage')){
										setZoomForActiveSlide();
									}
								}
							});
						});
					};
					var builSlider = function (){
						$slider.find('.rsSlide').removeClass('product-image');
						$curMainSlide = $slider.find('.rsActiveSlide');	
						var $newImg = $curMainSlide.find('.rsMainSlideImage');
						$curMainSlide.addClass('product-image');
						var $curThumb = $slider.find('.rsNavSelected img.rsTmb');
						if(!$slider.hasClass('rsFullscreen')){
							if( $curThumb.data('rsmainimg') != ''){
								$newImg.attr('src',$curThumb.data('rsmainimg'));
							}
							setZoomForActiveSlide();
						}
					};	
					var setSliderHeight = function(){
						if(!$slider.hasClass('rsFullscreen')){
							//var $mainImg = $slider.find('.rsActiveSlide .rsMainSlideImage');
							//var height = $mainImg.height();
							var height = 0;
							$('.rsImg',$element).each(function(){
								var $img = $(this);
								height = Math.max(height,$img.height());
							});
							$slider.height(height);
							$slider.find('.rsOverflow').height(height);
						}
					}
					slider.ev.on('rsAfterSlideChange', function(event) {
						builSlider();
					});
					slider.ev.on('rsAfterContentSet', function(e, slideObject) {
						$slider.find('.rsThumbs').returnImageSrc();
					});
					slider.ev.on('rsExitFullscreen', function() {
						$slider.addClass('no-fullscreen');
						$slider.find('.rsThumbs').returnImageSrc();
						var src = $slider.find('.rsNavSelected img').data('rsmainimg');
						$slider.find('.rsActiveSlide .magnify').css({background: 'url('+src+') no-repeat'});
						zoomImage();
					});
					slider.ev.on('rsAfterContentSet', function() {
						zoomImage();
						setSliderHeight();
					});
					slider.ev.on('rsEnterFullscreen', function(){
						$slider.removeClass('no-fullscreen');
						var height = $slider.find('.rsOverflow').height() + 'px';
						setTimeout(function(){
							$slider.find('.rsImg').css({'max-height' : height});
						},200);
					});
					$(window).off('resize.productMedia'+uniqid);
					$(window).on('resize.productMedia'+uniqid,function(){
						setTimeout(function(){
							setSliderHeight();
							if($slider.hasClass('rsFullscreen')){
								$slider.find('.rsOverflow').height('');
								var height = $slider.find('.rsOverflow').height() + 'px';
								$slider.find('.rsImg').css({'max-height' : height});	
							}
						},300);
					});
					if($(window).width() < adapt){
						slider.setThumbsOrientation('horizontal');
					}
					$(window).on('cdz_mobile.productMedia'+uniqid,function(){
						$slider.find('.rsOverflow').width('100%');
						slider.setThumbsOrientation('horizontal');
					});
					$(window).on('cdz_pc.productMedia'+uniqid,function(){
						$slider.find('.rsOverflow').width('');
						slider.setThumbsOrientation(thumbStyle);	
					});
				},
				_updateSwatchImages: function(){
					var self = this;
					$(window).on('swatchUpdateMainImage',function(event,image){
						var media = conf.media;
						$(media).each(function(id,el){
							if(el.isMain){
								media[id] = image;
							}
						});
						$element.removeData('royalSlider');
						self._loadMediaHtml( media );
						self._setUpMediaSlider( $element ,conf.thumbStyle);
					});	
					$(window).on('swatchUpdateImages',function(event,images){
						var media = images;
						$element.removeData('royalSlider');
						self._loadMediaHtml( conf.media );
						self._setUpMediaSlider( $element ,conf.thumbStyle);
					});	
				}
			}
			productMediaSetup._create();
		});
	}
	return $.fn.productMedia;
});