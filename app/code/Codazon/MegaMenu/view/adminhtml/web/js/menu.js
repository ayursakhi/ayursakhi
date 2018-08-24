/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery'], function($, domReady) {
	$.widget('codazon.megamenu', {
		options: {
			type: 'horizontal',
			fixedLeftParent: '.cdz-fix-left',
			dropdownEffect: 'normal',
			rtlClass: 'cdz-rtl-layout',
			verClass: 'cdz-vertical-menu',
			horClass: 'cdz-horizontal-menu',
			parent: '.parent',
			subMenu: '.groupmenu-drop, .cat-tree .groupmenu-nondrop',
			triggerClass: 'dropdown-toggle',
			stopClickOnPC: true,
			delay: 100,
			contPadding: false,
			responsive: {
				768: 'mobile',
			}
		},
		_create: function() {
			var self = this,
				$body = $('body'),
				conf = this.options;
			this.options.trigger = '.' + conf.triggerClass;
			if(self.element.parents(conf.fixedLeftParent).length == 0){
				self.element.parent().addClass(conf.fixedLeftParent.replace('.',''));
				var $fixedLeftParent = self.element.parent();
			}else{
				var $fixedLeftParent = self.element.parents(conf.fixedLeftParent).first();
			}
			if(conf.contPadding === false){
				conf.contPadding = parseInt($fixedLeftParent.css('padding-left'));
			}
			var $menu = self.element;
			if(conf.type == 0){
				self._dropdownWidthStyle();
			}
			if ($menu.hasClass(conf.horClass)) {
				if ($body.hasClass(conf.rtlClass) || ($menu.parents('.'+conf.rtlClass).length > 0)) {
					self._alignMenuRight(conf);
				} else {
					self._alignMenuLeft(conf);
				}
				self._assignControls()._listen();
			} else {
				self._alignMenuTop();	
			}
			self._currentMode = self._getMode();
			self._rebuildHtmlStructure();
			self._setupMenu();
			self._responsive();
			if (conf.type != 1) {
				self._dropdownEffect();
			}
			self._menuTabs();
		},
		
		_menuTabs: function() {
			var self = this;
			$('.menu-tabs',$(self.element)).each(function(){
				var $tabs = $(this);
				if($tabs.parents('.tab-item').length > 0){
					var $tabParent = $tabs.parents('.tab-item').first().find('.cdz-tab-pane').find(' > .groupmenu-drop-content').first();
					if(typeof $tabs.data('attached') === 'undefined'){
						var $liParent = $tabs.parents('.item').first();
						$tabs.data('attached',true);
						$liParent.prevAll().each(function(){
							var $prev = $(this);
							$prev.appendTo($tabParent);
							$prev.children().first().unwrap();
						});
						var checkTab = false;
						$tabs.prevAll().appendTo($tabParent);
						$tabs.appendTo($tabParent);
						$liParent.nextAll().each(function(){
							var $next = $(this);
							if($next.children('.menu-tabs').length > 0){
								checkTab = true; return false;
							}
						});
						if(!checkTab){
							$liParent.nextAll().each(function(){
								var $next = $(this);
								$next.appendTo($tabParent);
								$next.children().first().unwrap();
							});
						}
						$liParent.remove();
					}
				}
			});
			
			$('.menu-tabs',$(self.element)).each(function(){
				var $tabs = $(this);
				var html = '';
				if($tabs.parents('.menu-tabs').length > 0){
					var leftClass = 'col-sm-6';
					var rightClass = 'col-sm-18';
				}else{
					var leftClass = 'col-sm-5';
					var rightClass = 'col-sm-19';
				}
				html +=	'<div class="row cdz-tabs">';
				html +=		'<div class="'+($tabs.hasClass('cdz-vertical-tabs')?leftClass:'')+' cdz-nav-tabs"></div>';
				html +=		'<div class="'+($tabs.hasClass('cdz-vertical-tabs')?rightClass:'')+' cdz-tab-content"></div>';
				html +=	'</div>';
				var $tabInner = $(html);
				var $accordion = $('> .groupmenu-nondrop',$tabs);
				$tabInner.appendTo($tabs);
				var tabLinks = [];
				var tabPanes = [];
				
				$('> .tab-item',$accordion).each(function(index, element) {
					var $tabItem = $(this), $tabLink = $('> .cdz-link-wrap > .cdz-tab-link',$tabItem), $tabPane = $('> .cdz-tab-pane',$tabItem);
                    tabLinks.push($tabLink);
					tabPanes.push($tabPane);
                });
				
				$('> .tab-item',$accordion).each(function(){
					var $tabItem = $(this), $tabLink = $('> .cdz-link-wrap > .cdz-tab-link',$tabItem), $tabPane = $('> .cdz-tab-pane',$tabItem);
					$tabLink.on('mouseenter.cdztabs',
						function(e){
							$(tabLinks).each(function(){ $(this).removeClass('active') });
							$(tabPanes).each(function(){ $(this).removeClass('active') });
							$tabPane.addClass('active');
							$tabLink.addClass('active');
						}
					);
					$tabLink.appendTo($('> .cdz-nav-tabs',$tabInner));
					$tabPane.appendTo($('> .cdz-tab-content',$tabInner));
				});
				$('.cdz-tab-pane',$tabInner).first().addClass('active');
				$('.cdz-tab-link',$tabInner).first().addClass('active');
				$accordion.hide();
				
				function pcTabs(){
					$accordion.hide();
					$tabInner.show();
					$('> .tab-item',$accordion).each(function(id,el){
						var $tabItem = $(this);
						var $tabLink = tabLinks[id], $tabPane = tabPanes[id];
						$('.dropdown-toggle',$tabLink.parent()).remove();
						$tabLink.appendTo($('> .cdz-nav-tabs',$tabInner));
						$tabPane.appendTo($('> .cdz-tab-content',$tabInner));
						$tabPane.css('display','');
						$tabPane.removeClass('active');
						$tabLink.removeClass('active');
						$tabLink.on('mouseenter.cdztabs',
							function(e){
								$(tabLinks).each(function(){ $(this).removeClass('active') });
								$(tabPanes).each(function(){ $(this).removeClass('active') });
								$tabPane.addClass('active');
								$tabLink.addClass('active');
							}
						);
					});
					tabLinks[0].addClass('active');
					tabPanes[0].addClass('active');
				}
				function mbTabs(){
					$accordion.show();
					$tabInner.hide();
					$('> .cdz-nav-tabs > .cdz-tab-link',$tabInner).each(function(id,el){
						var $tabLink = $(this), $tabPane = tabPanes[id],
						$tabItem = $('> .tab-item:eq('+id+')',$accordion),
						$linkWrap = $('> .cdz-link-wrap',$tabItem);
						$tabLink.off('mouseenter.cdztabs');
						var $toggle = $('<span class="dropdown-toggle"></span>');
						$tabLink.appendTo($linkWrap)
						
						$toggle.appendTo($linkWrap);
						$tabPane.appendTo($tabItem);
						$tabPane.hide();
						$tabLink.removeClass('active');
						$tabPane.removeClass('active');
						$toggle.on('click',function(){
							$tabLink.toggleClass('active');
							$tabPane.slideToggle(200,function(){
								$tabPane.toggleClass('active');	
								$tabPane.height('');
							});
						});
					});
				}
				var currentMode = self._getMode();
				if (currentMode == 'mobile') {
					mbTabs();
				}
				function tabMinHeight(tabPanes){
					var maxHeight = 0;
					$(tabPanes).each(function(){
						$(this).css({minHeight:''});
						$(this).show();
					});
					$(tabPanes).each(function(id,el){
						var $childPane = $(tabPanes[id]).find('.cdz-tab-pane');
						if($childPane.length){
							tabMinHeight($childPane);
						}
						if($(tabPanes[id]).height() > maxHeight){
							maxHeight = $(tabPanes[id]).height();
						}
					});
					$(tabPanes).each(function(){
						$(this).css({minHeight:maxHeight});
						$(this).css('display','');
					});
				}
				$tabs.parents('li.level0').first().find('> .groupmenu-drop').on('animated',function(){
					tabMinHeight(tabPanes);
				});
				$tabs.parents('li.level0').hover(
					function(){
						if(self._getMode() != 'mobile'){
							switch(self.options.dropdownEffect){
								case 'slide':
								case 'fade':
									if(self.options.type == 1){
										setTimeout(function(){
											tabMinHeight(tabPanes);
										},150);
									}
									break;
								case 'normal':
									setTimeout(function(){
										tabMinHeight(tabPanes);
									},150);
									break;
								default:
									tabMinHeight(tabPanes);								
							}
						}
					},
					function(){
						$(tabPanes).each(function(){
							$(this).css({minHeight:''});
						});
					}
				);
					
				$(window).on('resize', function() {
					var mode = self._getMode();
					if (currentMode != mode) {
						currentMode = mode;
						if (mode == 'mobile') {
							mbTabs();
						} else {
							pcTabs();
						}
					}
				});
			});
		},
		_dropdownWidthStyle: function(){
			var self = this;
			var $win = $(window);
			$('body').addClass(fixedLeftClass);
			if(self.element.hasClass('dropdown-fullwidth')){
				self.options.fixedLeftParent = '.cdz-fullwidth-fix-left';
				var fixedLeftClass = self.options.fixedLeftParent.replace('.','');
				if(self.element.parents(self.options.fixedLeftParent).length == 0){
					$('body').addClass(fixedLeftClass);
				}
				var $container = self.element.parents(self.options.fixedLeftParent).first();
				$('li.item.level0',self.element).each(function(){
					
					var $li = $(this);
					var $dropdown = $('> .groupmenu-drop',$li);
					if( !$dropdown.hasClass('cat-tree') ){
						function setWidth(mode){
							if (mode == 'desktop') {
								$dropdown.width($container.outerWidth(true));
							} else {
								$dropdown.width('');
							}
						}
						var mode = self._getMode();
						setWidth(mode);
						$(window).on('resize', function() {
							mode = self._getMode();
							setWidth(mode);
						});
					}
				});
			}
		},
		_assignControls: function () {
            this.controls = {
                toggleBtn: $('[data-action="toggle-nav"]'),
                swipeArea: $('.nav-sections')
            };

            return this;
        },
		_listen: function () {
            var controls = this.controls;
            var toggle = this.toggle;
			controls.toggleBtn.unbind();
			controls.swipeArea.unbind();
            this._on(controls.toggleBtn, {'click': toggle});
            this._on(controls.swipeArea, {'swipeleft': toggle});
        },
		toggle: function () {
            if ($('html').hasClass('nav-open')) {           
                setTimeout(function () {
                   $('html').removeClass('nav-open');
                    $('html').removeClass('nav-before-open');
                }, 500);
            } else {
                $('html').addClass('nav-before-open');
                setTimeout(function () {
                    $('html').addClass('nav-open');
                }, 42);
            }
        },
		_currentMode: false,
		_dropdownEffect: function() {
			var self = this;
			var conf = this.options,
				effect = conf.dropdownEffect;
			switch (effect) {
				case 'translate':
				case 'slide':
				case 'fade':
				default:
					self._attachEffect(effect);
					break;
			}
		},
		_attachEffect: function(type) {
				var self = this,
					conf = this.options;
				var timeout = false;
				$('.level-top', self.element).each(function() {
					var $leveltop = $(this);
					var $drop = $leveltop.children('.groupmenu-drop');
					if (type != 'translate') {
						$drop.hide();
					}
					$drop.addClass('slidedown');
					$leveltop.on('mouseover', function() {
						$('item.level0',self.element).find(' > .groupmenu-drop').height('');
						if (timeout) clearTimeout(timeout);
						timeout = setTimeout(function() {
							if (self._currentMode == 'desktop') {
								if (type == 'slide') {
									$drop.css({'box-shadow':'none','border-bottom':'none'});
									$drop.stop().slideDown(400,'swing', function() {
										$leveltop.addClass('open');
										$drop.height('');
										$drop.trigger('animated');
										$drop.css({'box-shadow':'','border-bottom':''});
									});
								} else if (type == 'fade') {
									$drop.stop().fadeIn(400,'swing',function() {
										$leveltop.addClass('open');
										$drop.trigger('animated');
									});
								} else if (type == 'normal') {
									$drop.show().trigger('animated');
									$leveltop.addClass('open');
								}
								$leveltop.trigger('animated_in');
							}
						}, conf.delay);
					});
					$leveltop.on('mouseleave', function() {
						if (timeout) clearTimeout(timeout);
						if (self._currentMode == 'desktop') {
							if (type == 'slide') {
								$drop.stop().slideUp(200, function() {
									$leveltop.removeClass('open');
									$leveltop.trigger('animated_out');
								});
							} else if (type == 'fade') {
								$drop.stop().fadeOut(200, function() {
									$leveltop.removeClass('open');
									$leveltop.trigger('animated_out');
								});
							} else if (type == 'normal') {
								$drop.hide();
								$leveltop.removeClass('open');
								$leveltop.trigger('animated_out');
							}
						}
					});
				});
			},
		_desktopMenu: function(conf) {
			var $menu = this.element;
			var $subMenu = $(conf.subMenu, $menu);
			$subMenu.css('display', '');
			$subMenu.removeClass('open');
			$(conf.parent, $menu).removeClass('open');
			$(conf.trigger, $menu).remove();
		},
		_mobileMenu: function(conf) {
			var $menu = this.element;
			$(conf.subMenu, $menu).hide();
			$(conf.parent, $menu).each(function() {
				var $li = $(this);
				$li.children(conf.subMenu).each(function() {
					var $subMenu = $(this);
					var $toggle = $('<span class="' + conf.triggerClass + '" />');
					$toggle.insertBefore($subMenu);
					$toggle.on('click.showsubmenu', function() {
						$li.toggleClass('open');
						$subMenu.toggleClass('open');
						$subMenu.slideToggle(300);
					});
				});
			});
		},
		_getAdapt: function() {
			var responsive = this.options.responsive,
				$win = $(window),
				winWidth = $win.prop('innerWidth'),
				minWidth = 0;
			for (adapt in responsive) {
				if ((minWidth <= winWidth) && (winWidth < adapt)) {
					return adapt;
				}
				minWidth = adapt;
			}
			return false;
		},
		_getMode: function() {
			responsive = this.options.responsive;
			$win = $(window);
			var winWidth = $win.prop('innerWidth');
			var minWidth = 0;
			var adapt = this._getAdapt();
			if (adapt !== false) {
				return responsive[adapt];
			} else {
				return 'desktop';
			}
		},
		_setupMenu: function() {
			var mode = this._getMode();
			if (mode == 'mobile') {
				this._mobileMenu(this.options);
			} else {
				this._desktopMenu(this.options);
			}
		},
		_responsive: function() {
			var self = this;
			$(window).on('resize', function() {
				var mode = self._getMode();
				if (self._currentMode != mode) {
					self._currentMode = mode;
					self._setupMenu();
				}
			});
		},
		_rebuildHtmlStructure: function() {
			var self = this;
			$('.need-unwrap', self.element).each(function() {
				var $this = $(this);
				$this.children('.groupmenu-drop').removeClass('groupmenu-drop').addClass('groupmenu-nondrop');
				var $parent = $(this).parent();
				var $newDiv = $('<div />');
				$newDiv.appendTo($parent);
				$newDiv.attr('class', $this.attr('class'));
				$newDiv.attr('style', $this.attr('style'));
				$this.children().appendTo($newDiv);
				$this.remove();
			});
			$('.no-dropdown', self.element).each(function() {
				var $noDropdown = $(this);
				$('.need-unwrap', $noDropdown).first().unwrap();
				$('.need-unwrap', $noDropdown).removeClass('need-unwrap');
				$noDropdown.children('.groupmenu-drop').removeClass('groupmenu-drop').addClass('groupmenu-nondrop');
			});
		},
		_getEventIn: function(conf) {
			if (this.options.type == 'translate') {
				return 'mouseover mouseenter';
			} else {
				return 'animated_in';
			}
		},
		_getEventOut: function(conf) {
			if (this.options.type == 'translate') {
				return 'mouseleave';
			} else {
				return 'animated_out';
			}
		},
		_alignMenuLeft: function(conf) {
			var self = this;
			var $menuCont = self.element.parents(conf.fixedLeftParent).first();
			function handlerIn($li){
				var $dropdown = $li.children('.groupmenu-drop').first();
				//$dropdown.css('left',0);
				var dWidth = $dropdown.outerWidth(), dOffset = $li.offset().left,
				cWidth = $menuCont.width(), cOffset = $menuCont.offset().left,
				dRightBound = dOffset + dWidth,
				cRightBound = cOffset + cWidth,
				overFlow = dRightBound - cRightBound;
				if(overFlow > 0){
					var relativeLeft = dOffset - cOffset;
					
					var adjustment = self.element.offset().left - cOffset;
					if( adjustment > 10 ){
						adjustment = 0;
					} 
					if( (cWidth - dWidth) <= 20 && ((cWidth - dWidth) > 0) ){
						adjustment = (cWidth - dWidth)/2;
					}
					var left = -Math.min(relativeLeft,overFlow) - Math.max(0,adjustment);
					$dropdown.css({left:left});
				}					
			}
			function handlerOut($li){
				//$li.children('.groupmenu-drop').css('left', '');
			}
			var $li = $(' > .groupmenu > .level-top.parent > .groupmenu-drop', self.element).parent();
			$li.each(function(){
				var $dropdown = $li.children('.groupmenu-drop');
				var $curLi = $(this);
				handlerIn($curLi);
				$(window).load(function(){
					handlerIn($curLi);
				});
				var timeoute = false;
				$(window).on('resize',function(){
					$dropdown.css('left','');
					if(timeoute) clearTimeout(timeoute);
					timeoute = setTimeout(function(){
						handlerIn($curLi);
					},300);
				});
			});
			if (this.options.type == 'translate') {				
				$li.hover(function() {
					handlerIn($(this));
				}, function() {
					handlerIn($(this));
				});
			} else {
				var eventIn = this._getEventIn(),
				eventOut = this._getEventOut();
				$li.on(eventIn,function(){
					handlerIn($(this));
				}).on(eventOut, function() {
					handlerIn($(this));
				});
			}
		},
		_alignMenuRight: function(conf) {
			var self = this;
			var eventIn = this._getEventIn(),
				eventOut = this._getEventOut();
			var $menuCont = self.element.parents(conf.fixedLeftParent).first();
			function handlerIn($li){
				var $dropdown = $li.children('.groupmenu-drop').first();
				var dWidth = $dropdown.outerWidth(), lOffset = $li.offset().left, lWidth = $li.outerWidth(true),
				cWidth = $menuCont.width(), cOffset = $menuCont.offset().left,
				dLeftBound = lOffset + lWidth - dWidth,
				cLeftBound = cOffset,
				overFlow = cLeftBound - dLeftBound;
				var dLeft = -(dWidth - lWidth);
				if(overFlow > 0){
					var relativeLeft = lOffset - cOffset;
					var adjustment = 0;
					var adjustment = self.element.offset().left - cOffset;
					if( adjustment > 10 ){
						adjustment = 0;
					}
					if( (cWidth - dWidth) <= 20 && ((cWidth - dWidth) > 0) ){
						adjustment = (cWidth - dWidth)/2;
					}
					var left = -Math.min(relativeLeft,-dLeft-overFlow) + Math.max(0,adjustment);
					$dropdown.css({left:left,right:'auto'});
				}else{
					$dropdown.css({left:dLeft,right:'auto'});
				}
			}
			
			function handlerOut($li){
				//$li.children('.groupmenu-drop').css('right', '');
			}
			var $li = $(' > .groupmenu > .level-top > .groupmenu-drop', self.element).parent();
			$li.each(function(){
				var $dropdown = $li.children('.groupmenu-drop');
				var $curLi = $(this);
				handlerIn($curLi);
				$(window).load(function(){
					handlerIn($curLi);
				});
				var timeoute = false;
				$(window).on('resize',function(){
					$dropdown.css('left','');
					if(timeoute) clearTimeout(timeoute);
					timeoute = setTimeout(function(){
						handlerIn($curLi);
					},300);
				});
			});
			if (this.options.type == 'translate') {
				$li.hover(function() {
					handlerIn($(this));
				}, function() {
					handlerIn($(this));
				});
			} else {
				var eventIn = this._getEventIn(),
				eventOut = this._getEventOut();
				$li.on(eventIn,function(){
					handlerIn($(this));
				}).on(eventOut, function() {
					handlerIn($(this));
				});
			}
		},
		_alignMenuTop: function(conf) {
			var self = this;
			var $win = $(window);
			$(' > .groupmenu > .level-top > .groupmenu-drop', self.element).parent().hover(
			function(){
				var $li = $(this);
				var $ddMenu = $(this).children('.groupmenu-drop');
				$ddMenu.css({top:''});
				var winHeight = $win.height(),
				winTop = $(window).scrollTop();
				ddTop = $ddMenu.offset().top, ddHeight = $ddMenu.outerHeight(),
				overflow = (ddTop - winTop + ddHeight) > winHeight;
				if($li.hasClass('fixtop')){
					var newTop = parseInt($ddMenu.css('top')) - (ddTop - self.element.children('.groupmenu').offset().top);
					$ddMenu.css({top: newTop});
				}else if(overflow){
					var newTop1 = parseInt($ddMenu.css('top')) - (ddTop - winTop + ddHeight - winHeight);
					var newTop2 = parseInt($ddMenu.css('top')) - (ddTop - self.element.children('.groupmenu').offset().top);
					var newTop = Math.max(newTop1,newTop2); 
					$ddMenu.css({top: newTop});
				}
			},
			function(){});
		}
	});
	return $.codazon.megamenu;
});