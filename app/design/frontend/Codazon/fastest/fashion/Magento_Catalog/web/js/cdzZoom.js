define([
	'jquery'
],function($){
	$.fn.cdzZoom = function(options){
		var defaultConfig = {
			mainImg: '.fotorama__img',
			magnify: '.magnify'
		};
		var conf = $.extend({},defaultConfig,options);
		return this.each(function(){
			var $this = $(this);
			var $mainImg = $(conf.mainImg,$this);
			var src = $mainImg.attr('src');
			if(typeof src !== 'undefined'){
				if( $('.magnify',$this).length == 0 ){
					var $magnify = $('<div class="magnify" style="background: url(\''+src+'\') no-repeat; width:225px; height:225px;" ></div>');
					$magnify.appendTo($this);
					
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
								var dx = $mainImg.offset().left - $this.offset().left;
								var dy = $mainImg.offset().top - $this.offset().top;
								var rx = Math.round(mx/$mainImg.width()*nativeWidth - $magnify.width()/2)*(-1) + dx;
								var ry = Math.round(my/$mainImg.height()*nativeHeight - $magnify.height()/2)*(-1) + dy;
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
				}
			}
		});
	}
});