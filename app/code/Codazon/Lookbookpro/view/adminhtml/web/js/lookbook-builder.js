define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'mage/template',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/adminhtml/wysiwyg/widget'
], function (_, jQuery, mageTemplate, rg, Abstract, confirm, alert) {
    jQuery(function($) {
        $.widget('codazon.lookbookBuilder', {
            options: {
                elementName: '',
                btnAddImage: '[data-role=lb-add-image]',
                btnAddProd: '[data-role=lb-add-product]',
                btnReset: '[data-role=lb-reset]',
                imgWrap: '[data-role=image-wrap]',
                dataPoints: '[data-role=data-points]',
                pdPoint: '[data-role=pd-point]',
                canvas: '[data-role=canvas]',
                insertProductText: 'Insert Product',
                input: null
            },
            _create: function() {
                var self = this, conf = this.options;
                this._assignVariables();
                this._bindEvents();
            },
            _assignVariables: function() {
                var self = this, conf = this.options;
                this.$btnAddImage = $(conf.btnAddImage, this.element);
                this.$btnAddProd = $(conf.btnAddProd, this.element);
                this.$imgWrap = $(conf.imgWrap, this.element);
                this.$dataPoints = $(conf.dataPoints, this.element);
                this.$input = conf.input;
                this.$canvas = $(conf.canvas, this.element);
                this._createForm();
                this._initCanvas();
            },
            _createForm: function() {
                var self = this, conf = this.options;
                this.$formWrap = $('<div>').css('display', 'none').appendTo('body');
                
                this.$iframe = $('<iframe>', {
                    name: 'upload_iframe_' + conf.elementName
                }).appendTo(this.$formWrap);
                
                this.$productInput = $('<input>', {
                    id: 'product_input_' + conf.elementName + 'value',
                    type: 'hidden',
                }).appendTo(this.$formWrap);
                
                this.$productLabel = $('<div>', {
                    id: 'product_input_' + conf.elementName + 'label',
                }).appendTo(this.$formWrap);
                
                this.$form = $('<form>', {
                    target: 'upload_iframe_' + conf.elementName,
                    method: 'post',
                    enctype: 'multipart/form-data',
                    class: 'ignore-validate',
                    action: conf.uploadUrl
                }).appendTo(this.$formWrap);
                this.$inputFile = $('<input>', {
                    type: 'file',
                    name: 'datafile',
                    class: 'lookbook_file'
                }).appendTo(this.$form);
                $('<input>', {
                    type: 'hidden',
                    name: 'form_key',
                    value: FORM_KEY
                }).appendTo(this.$form);
            },
            _bindEvents: function() {
                var self = this, conf = this.options;
                this.element.on('click', conf.btnAddImage, function() {
                    self.$inputFile.click();
                });
                this.$inputFile.on('change', function() {
                    $('body').loader('show');
                    var iframeHandler = function () {
                         $('body').loader('hide');
                         var imageParams = $.parseJSON($(this).contents().find('body').html()),
                         fullMediaUrl = imageParams['swatch_path'] + imageParams['file_path'];
                         if (self.$imgWrap.find('img').length == 0) {
                             self.$imgWrap.append($('<img>'));
                         }
                         var $img = self.$imgWrap.find('img');
                         $img.attr('src', fullMediaUrl);
                         $img.attr('data-filepath', imageParams['file_path']);
                         
                         var imgObj = new Image();
                         $(imgObj).load(function() {
                            var width = imgObj.width, height = imgObj.height;
                            $img.attr('data-width', width);
                            $img.attr('data-height', height);
                         });
                         imgObj.src = fullMediaUrl;
                    };
                    self.$iframe.off('load');
                    self.$iframe.load(iframeHandler);
                    self.$form.submit();
                    $(this).val('');
                });
                this.element.on('click', conf.btnAddProd, function() {
                    if (self.$imgWrap.find('img').length) {
                        self._addNewPoint(null, 0, 0);
                    } else {
                        alert({
                            title: 'Message',
                            content: 'You need to insert an image first!'
                        });
                    }
                });
                this.element.on('click', conf.btnReset, function() {
                    confirm({
                        title: 'Reset',
                        content: 'Are you sure to reset? Item image and all added products will be removed.',
                        actions: {
                            confirm: function() {
                                self.$imgWrap.empty();
                                self.$dataPoints.empty();
                            }
                        }
                    });
                });
                $('body').on('processStart', function(e) {
                    var json = self._toJson();
                    self.$input.val(JSON.stringify(json)).trigger('change');
                });
                this.$productInput.on('product_updated', function() {
                    if (self.$currentPoint && self.$productInput.val()) {
                        if (self.$currentPoint.find('.product-name').length == 0) {
                            self.$currentPoint.find('.pd-point-inner').append($('<div class="product-name">'));
                        }
                        var $productName = self.$currentPoint.find('.product-name'),
                        productName = self.$productLabel.text(),
                        productId = self.$productInput.val();
                        productId = productId.split('/');
                        productId = productId[1];
                        productName = '<strong>' + productId + ':</strong> ' + productName.split('/').last();
                        
                        self.$currentPoint.attr('data-productId', productId);
                        $productName.html(productName);
                        $productName.fadeIn(300, 'linear', function() {
                            setTimeout(function() {
                                $productName.fadeOut(300, 'linear', function() {
                                    $productName.css('display', '');
                                });
                            }, 500);
                        });
                        self.$currentPoint = false;
                    }
                });
            },
            _addNewPoint: function(productId, left, top) {
                var self = this, conf = this.options;
                var $newPoint = $('<div>', {
                    'data-role': 'pd-point',
                    'class': 'pd-point',
                    'data-productId': productId
                }).css({
                    left: left,
                    top: top
                });
                $newPoint.appendTo(self.$dataPoints);
                var $inner = $('<div class="pd-point-inner">').appendTo($newPoint);
                var $addProduct = $('<div class="pd-add-product" title="Select product"></div>').appendTo($inner);
                var $rmPoint = $('<div class="pd-point-rm" title="Remove this point">').appendTo($inner);
                if (productId) {
                    productName = '<strong>' + productId + ':</strong> ' + codazon.products[productId].name;
                    $inner.append($('<div class="product-name">').html(productName));
                }
                
                $rmPoint.on('click', function(){
                    confirm({
                        title: 'Delete Point',
                        content: 'Are you sure to remove this point?',
                        actions: {
                            confirm: function() {
                                $newPoint.remove();
                            }
                        }
                    });
                });
                $addProduct.on('click', function() {
                    self.$currentPoint = $newPoint;
                    self._pickProduct($newPoint);
                });
                $newPoint.draggable({ containment: self.$dataPoints, scroll: false});
            },
            _pickProduct: function($newPoint) {
                var self = this, conf = this.options;
                if (!window.productChooser) {
                    window.productChooser = new WysiwygWidget.chooser(
                        'product_input_' + conf.elementName,
                        codazon.productUrl + '?uniq_id=' + 'productChooser',
                        {"buttons":{"open":"Select Product...","close":"Close"}}
                    );
                    window.productChooser.oldClose = window.productChooser.close;
                    window.productChooser.close = function(value) {
                        window.productChooser.oldClose();
                        $(this.getElement()).trigger('product_updated');
                    };
                }
                window.productChooser.choose();
            },
            _toJson: function() {
                var self = this, conf = this.options;
                var result = {};
                if (this.$imgWrap.find('img').length) {
                    var $img = this.$imgWrap.find('img');
                    result.image = $img.data('filepath');
                    result.width = $img.data('width');
                    result.height = $img.data('height');
                } else {
                    result.image = null;
                    result.width = 0;
                    result.height = 1;
                }
                result.points = [];
                var maxwidth = parseFloat(this.$canvas.width());
                var maxheight = parseFloat(this.$canvas.height());
                this.$dataPoints.find(conf.pdPoint).each(function() {
                    var $point = $(this), left = $point.css('left'), top = $point.css('top');
                    left = 100*parseFloat(left)/maxwidth;
                    top = 100*parseFloat(top)/maxheight;
                    var product = {
                        left: left,
                        top: top,
                        productId: $point.attr('data-productid')
                    }
                    result.points.push(product);
                });
                return result;
            },
            _initCanvas: function(){
                var self = this, conf = this.options;
                var data = this.$input.val()? JSON.parse(this.$input.val()) : null;
                if (data) {
                    var image = data.image;
                    if (image) {
                        var fullMediaUrl = codazon.mediaUrl + conf.mediaUrl + image;
                        var $img = $('<img>', {
                            'src': fullMediaUrl,
                            'data-filepath': image
                        }).appendTo(self.$imgWrap);
                         var imgObj = new Image();
                         $(imgObj).load(function() {
                            var width = imgObj.width, height = imgObj.height;
                            $img.attr('data-width', width);
                            $img.attr('data-height', height);
                         });
                         imgObj.src = fullMediaUrl;
                    }
                    if (data.points.length) {
                        $.each(data.points, function(i, point) {
                            self._addNewPoint(point.productId, point.left + '%', point.top + '%');
                        });
                    }
                }
            }
        });
    });
    
    return Abstract.extend({
        defaults: {
            elementId: 0,
            prefixName: '',
            prefixElementName: '',
            elementName: '',
            value: '',
            uploadUrl: ''
        },
        initialize: function () {
            this._super().initOldCode();
            return this;
        },
        initConfig: function () {
            this._super();
            //this._configureDataScope();
            return this;
        },
        initOldCode: function(){
            var self = this;
            jQuery.async('#' + this.uid, function(input) {
                var $input = jQuery(input);
                var $parent = $input.parents('[data-role=lookbook-builder]').first();
                var options = {
                    elementName: self.inputName,
                    input: $input,
                    uploadUrl: self.uploadUrl,
                    mediaUrl: self.mediaUrl
                };
                jQuery.codazon.lookbookBuilder(options, $parent);
            });
        }
    });
});