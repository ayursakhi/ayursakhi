/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "matchMedia",
    "Codazon_AjaxLayeredNav/js/nouislider.min",
    "jquery/ui",
    'Codazon_AjaxLayeredNav/js/layout'
    ], function ($, mediaCheck, noUiSlider) {
    'use strict';
    function setParam(data, name, value){
        var tmp = '';
        var flag = false;
        var result = '';
        if(data){
            tmp = data.split('&');
        }
        if(tmp.length > 0){
            for(var i = 0; i < tmp.length; i++){
                var tmp2 = tmp[i].split('=');
                if(tmp2.length > 1 && tmp2[0] == name)
                {
                    tmp2[1] = value;
                    flag = true;
                }
                if(tmp2.length > 1){
                    result += ('&' + tmp2[0] + '=' + tmp2[1]);
                }
            }
        }
        if(!flag){
            result += ('&' + name + '=' + value);
        }
        return result;
    }
    function changeUrl(paramName, paramValue, defaultValue) {
        var decode = window.decodeURIComponent,
            urlPaths = window.location.href.split('#'),
            urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
            paramData = {},
            parameters, i;
        for (i = 0; i < urlParams.length; i++) {
            parameters = urlParams[i].split('=');
            paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                decode(parameters[1].replace(/\+/g, '%20')) :
                '';
        }
        paramData[paramName] = paramValue;

        if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
            delete paramData[paramName];
        }
        paramData = $.param(paramData);
        
        var param = (paramData.length ? paramData : '');
        if(paramData.length){
            window.location.href = urlPaths[0] + "#" + param;
        }else{
            window.location.hash = '';
        }
        return param;
    }
    function request(param){
        var headerHeight = $('.page-header:first').height();
        $(window).scrollTop(headerHeight);
        displayOverlay('Loading...');
        param = convertCodeToId(param);
        $.ajax({
            url: url + '?' + catparam + '&' + param,
            //context: $('#page-title-heading'),
            cache:true
        }).success(function(json) {
            var data = jQuery.parseJSON(json);
            var data = jQuery.parseJSON(json);
            var $productListing = $('<div>');
            $productListing.html(data.products);
            $('#layered-filter-block').replaceWith(data.layer);
            if ($('.column.main .products.wrapper').length) {
                $('.column.main .toolbar-products:first').replaceWith($productListing.find('.toolbar-products').first());
                $('.column.main .toolbar-products:last').replaceWith($productListing.find('.toolbar-products').last());
                $('.column.main .products.wrapper').replaceWith($productListing.find('.products.wrapper'));
            } else {
                var $productContent = $productListing.children();
                $('.column.main').append($productContent);
            }
            if ($productListing.find('.message.info.empty').length) {
                $('.column.main').prepend($productListing.find('.message.info.empty'));
            }
            $productListing.find('script').clone().appendTo('body');
            $productListing.find('style').clone().appendTo('body');
            $productListing.remove();
            //initAddToCart();
            $('.column.main').trigger("contentUpdated");
            //$('[data-role=tocart-form]').catalogAddToCart();
            initPriceSlider();
            initClickAjax();
            modeControl();
            orderControl();
            directionControl();
            limitControl();
            initQuickshop();
            initBtnMobileFilter();
            
            var configs = {"openedState": "active", "collapsible": true, "active": false, "collateral": { "openedState": "filter-active", "element": "body" } };
            $("#layered-filter-block").collapsible(configs);
            configs = {"openedState": "active", "collapsible": true, "active": false };
            $(".filter-current").collapsible(configs);
            configs = {"openedState": "active", "collapsible": true, "active": false, "multipleCollapsible": false};
            $("#narrow-by-list").accordion(configs);

            removeOverlay();
            setTimeout(sameHeightItems,500);
        });
    }
    $.widget('codazon.ajaxlayer', {
		options: {
			url: "",
			jsonFilter: "",
			catparam: "",
			uriRequest: "",
			uriShow: "",
			baseUrl: "",
            responsive: true,
            expanded: false,
            delay: 300,
        },

        _create: function () {
            var self = this;
            //this._super();
        },

        _init: function () {
            //this._super();
            this.delay = this.options.delay;

            if (this.options.expanded === true) {
                //this.isExpanded();
            }

            var href = window.location.href;
	        var params = href.split('#');
	        var param = catparam;
            $('.toolbar-products').removeAttr('data-mage-init');
	        if(params[1]){
	            param += this.convertCodeToId(params[1]);
	            this.request(param);
	        }else{
	            this.uriRequest = param;
                this.initPriceSlider();
	            this.initClickAjax();
	            this.initChangeOrderByAjax();
	            this.initSortOrderAjax();
	            this.initLimiterAjax();
                this.initAjaxModeSwitcher();
	        }
        },
        displayOverlay: function(text) {
            $("<div id='overlay'><div class='rectangle-bounce'><div class='rect1'></div><div class='rect2'></div><div class='rect3'></div><div class='rect4'></div><div class='rect5'></div></div></div>").css({
                "position": "fixed",
                "top": "0px",
                "left": "0px",
                "width": "100%",
                "height": "100%",
                "background-color": "rgba(0,0,0,.5)",
                "z-index": "10000",
                "vertical-align": "middle",
                "text-align": "center",
                "color": "#fff",
                "font-size": "40px",
                "font-weight": "bold",
                "cursor": "wait"
            }).appendTo("body");
        },

        removeOverlay: function() {
            $("#overlay").remove();
        },        
        setParam: function(data, name, value){
            var tmp = '';
            var flag = false;
            var result = '';
            if(data){
                tmp = data.split('&');
            }
            if(tmp.length > 0){
                for(var i = 0; i < tmp.length; i++){
                    var tmp2 = tmp[i].split('=');
                    if(tmp2.length > 1 && tmp2[0] == name)
                    {
                        tmp2[1] = value;
                        flag = true;
                    }
                    if(tmp2.length > 1){
                        result += ('&' + tmp2[0] + '=' + tmp2[1]);
                    }
                }
            }
            if(!flag){
                result += ('&' + name + '=' + value);
            }
            return result;
        },
        
        convertIdToCode: function(data)
        {
            var tmp = '';
            var result = '';
            if(data){
                tmp = data.split('&');
            }
            if(tmp.length > 0){
                for(var i = 0; i < tmp.length; i++){
                    var attr = tmp[i].split('=');
                    if(attr.length > 1)
                    {
                        var code = attr[0];
                        var value = attr[1];
                        if(jsonFilter.hasOwnProperty(code) && code != 'price'){
                            value = jsonFilter[code][value];
                        }
                        result += ('&' + code + '=' + value);
                    }
                }
            }
            return result;
        },
        
        convertCodeToId: function(data)
        {
            var tmp = '';
            var result = '';
            if(data){
                tmp = data.split('&');
            }
            if(tmp.length > 0){
                for(var i = 0; i < tmp.length; i++){
                    var attr = tmp[i].split('=');
                    if(attr.length > 1)
                    {
                        var code = attr[0];
                        var value = attr[1];
                        if(jsonFilter.hasOwnProperty(code) && code != 'price'){
                            //value = jsonFilter[code][value];
                            for(var i in jsonFilter[code]){
                                if(jsonFilter[code][i] == value){
                                    value = i;
                                    break;
                                }
                            }
                        }
                        result += ('&' + code + '=' + value);
                    }
                }
            }
            return result;
        },

        setSliderHandle: function(i, value) {
            var r = [null,null];
            r[i] = value;
            var keypressSlider = document.getElementById('price-slider');
            keypressSlider.noUiSlider.set(r);
        },

        initPriceSlider: function(){
            var keypressSlider = document.getElementById('price-slider');
            var input0 = document.getElementById('price-from');
            var input1 = document.getElementById('price-to');
            var inputs = [input0, input1];
            if (typeof priceRange === "undefined" || ! keypressSlider){
                return;
            }
            noUiSlider.create(keypressSlider, {
                start: [priceRange.min, priceRange.max],
                connect: true,
                tooltips: false,
                range: {
                    'min': priceRange.min,
                    'max': priceRange.max
                }
            },true);

            keypressSlider.noUiSlider.on('update', function( values, handle ) {
                if(inputs[handle]){
                    inputs[handle].value = values[handle];
                }
            });

            // Listen to keydown events on the input field.
            inputs.forEach(function(input, handle) {

                input.addEventListener('change', function(){
                    this.setSliderHandle(handle, this.value);
                });

                input.addEventListener('keydown', function( e ) {

                    var values = keypressSlider.noUiSlider.get();
                    var value = Number(values[handle]);

                    // [[handle0_down, handle0_up], [handle1_down, handle1_up]]
                    var steps = keypressSlider.noUiSlider.steps();

                    // [down, up]
                    var step = steps[handle];

                    var position;

                    // 13 is enter,
                    // 38 is key up,
                    // 40 is key down.
                    switch ( e.which ) {

                        case 13:
                            this.setSliderHandle(handle, this.value);
                            break;

                        case 38:

                            // Get step to go increase slider value (up)
                            position = step[1];

                            // false = no step is set
                            if ( position === false ) {
                                position = 1;
                            }

                            // null = edge of slider
                            if ( position !== null ) {
                                this.setSliderHandle(handle, value + position);
                            }

                            break;

                        case 40:

                            position = step[0];

                            if ( position === false ) {
                                position = 1;
                            }

                            if ( position !== null ) {
                                this.setSliderHandle(handle, value - position);
                            }

                            break;
                    }
                });
            });

            $("#apply-price").click(function(){
                var val = $("#price-from").val() + "-" + $("#price-to").val();
                var param = changeUrl('price', val);
                request(param);
                //getHref();
                //$('#price-fake').click();
            });
        },
        //==== end price slider ====
        
        initSortOrderAjax: function(){
            var self = this;
            $('[data-role="direction-switcher"]').removeAttr('data-role').click(function(e){
                e.preventDefault();
                if($(this).hasClass('sort-asc')){
                    var href = self.uriRequest;
                    var param = href +'&product_list_dir=desc';
                    self.request(param);
                    var tmp = param.replace(catparam,'');
                    tmp = tmp.replace('undefined','');
                    window.location.href = '#'+tmp;
                    return false;
                }else if($(this).hasClass('sort-desc')){
                    var href = self.uriRequest;
                    var param = href.replace('&product_list_dir=desc','');
                    self.request(param);
                    var tmp = param.replace(catparam,'');
                    tmp = tmp.replace('undefined','');
                    self.uriShow = self.convertIdToCode(tmp);
                    window.location.href = '#'+self.uriShow;
                    return false;
                }
            });
        },
        
        initChangeOrderByAjax: function(){
            var self = this;
            $('[data-role="sorter"]').removeAttr('data-role').on('change', function(e) {
                e.preventDefault();
                var href = self.uriRequest;
                var param = setParam(href,'product_list_order',$(this).val());;
                self.request(param);
                var tmp = param.replace(catparam,'');
                tmp = tmp.replace('undefined','');
                self.uriShow = self.convertIdToCode(tmp);
                window.location.href = '#'+self.uriShow;
                return false;
            });
        },
        
        initLimiterAjax: function(){
            var self = this;
            $('[data-role="limiter"]').removeAttr('data-role').on('change', function() {
                var href = self.uriRequest;
                var param = setParam(href,'product_list_limit',$(this).val());;
                self.request(param);
                var tmp = param.replace(catparam,'');
                tmp = tmp.replace('undefined','');
                self.uriShow = self.convertIdToCode(tmp);
                window.location.href = '#'+self.uriShow;
                return false;
            });
        },
        
        initClickAjax: function(){
            var obj = this;
            $('#layered-filter-block a').each(function(){
                $(this).click(function(){
                    var href = $(this).attr('href');
                    var params = href.split('?');
                    var param = catparam + "&" + params[1];
                    obj.request(param);
                    var tmp = params[1].replace(catparam,'');
                    tmp = tmp.replace('undefined','');
                    obj.uriShow = obj.convertIdToCode(tmp);
                    window.location.href = '#'+obj.uriShow;
                    return false;
                });
            });
            $('.pages a').each(function(){
                $(this).click(function(){
                    var href = $(this).attr('href');
                    var params = href.split('?');
                    var param = catparam + "&" + params[1];
                    obj.request(param);
                    var tmp = params[1].replace(catparam,'');
                    tmp = tmp.replace('undefined','');
                    obj.uriShow = obj.convertIdToCode(tmp);
                    window.location.href = '#'+obj.uriShow;
                    return false;
                });
            });
        },
        initQuickshop: function(){
            var configs = {
    		    "baseUrl": baseUrl,
    		    "qsLabel": "Quick Shop",
    		    "itemClass": ".product-item",
    		    "target": ".product-item-info",
    		    "autoAddButtons":true			
    		};
            requirejs(['Codazon_QuickShop/js/quickshop'],function(quickshop){
                quickshop(configs,$('body'));
            }); 
        },
        
        initAddToCart: function(){
            var configs = {
    		    "baseUrl": baseUrl,
    		    "qsLabel": "Quick Shop",
    		    "itemClass": ".product-item",
    		    "target": ".product-item-info",
    		    "autoAddButtons":true			
    		};
            requirejs(['catalogAddToCart'],function(catalogAddToCart){
                catalogAddToCart({},$('[data-role=tocart-form], .form.map.checkout'));
            }); 
        },
        initAjaxModeSwitcher: function(){
            $('[data-role="mode-switcher"]').removeAttr('data-role');
            $.codazon.categoryLayout({},$('body'));
        },
        request: function(param){
            var obj = this;
            this.displayOverlay('Loading...');
            this.uriRequest = param;
            $.ajax({
                url: url+'?'+param,
                cache:true
            }).done(function(json) {
                var data = jQuery.parseJSON(json);
                if ($('body').hasClass('page-layout-1column')) {
                    $('.column.main:first').html(data.layer + data.products);
                } else {
                    $('#toolbar-wrap').remove();
                    $('.columns .main:first').html(data.products);
                    $('#layered-filter-block').replaceWith(data.layer);
                }
                obj.initPriceSlider();
                obj.initClickAjax();
                obj.initChangeOrderByAjax();
                obj.initSortOrderAjax();
                obj.initLimiterAjax();
                obj.initAjaxModeSwitcher();
                obj.removeOverlay();
                
                $('#layered-filter-block').trigger('contentUpdated',null);
                
            });
        }
    });
    return $.codazon.ajaxlayer;
});