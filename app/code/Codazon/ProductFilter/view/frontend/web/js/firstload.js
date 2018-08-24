define([
    "jquery","jquery/ui","mage/dataPost", "cdz_slider",'domReady!','catalogAddToCart',
],function ($) {
    $.widget('codazon.FirstLoad', {
        options: {
            trigger: '.cdz-ajax-trigger',
            itemsWrap: '.product-items',
            ajaxLoader: '.ajax-loader',
            ajaxUrl: null,
            jsonData: null,
            currentUrl: '' 
        },
        _currentPage: 1,
        _checkVisible: function(){
            return (this.element.get(0).offsetWidth > 0) & (this.element.get(0).offsetHeight > 0) & (this.element.is(':visible'));
        },
        _create: function() {
            var self = this;
            if(self._checkVisible()){
                self._ajaxFirstLoad();
            }else{
                var interval = setInterval(function(){
                    if(self._checkVisible()) {
                        clearInterval(interval);
                        self._ajaxFirstLoad();
                    }
                },500);
            }
        },
        _ajaxFirstLoad: function() {
            var self = this;
            var config = this.options;
            config.jsonData.current_url = config.currentUrl;
            $.ajax({
                url: config.ajaxUrl,
                type: "POST",
                data: config.jsonData,
                cache: false,
                success: function(res){
                    if(res.html) {
                        self.element.html(res.html);
                    }
                    
                    $(".tocompare").dataPost();
                    self.element.trigger('contentUpdated');
                    $.fn._buildSlider();    
                    $.fn._tooltip();
                    $("[data-role=tocart-form], .form.map.checkout").catalogAddToCart({});
                    self.element.find("input[name*='form_key']").remove();
                    self.element.find("form").prepend('<input name="form_key" type="hidden" value="' + ($( "input[name*='form_key']" ).val()) + '">');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    console.error(textStatus);
                }
            });
        }
    });
    return $.codazon.FirstLoad;
});
