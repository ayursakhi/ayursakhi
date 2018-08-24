/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery', 'underscore', 'jquery/ui'], function ($, _) {
    'use strict';

    /**
     * Parse params
     * @param {String} query
     * @returns {{}}
     */
    $.parseParams = function (query) {
        var re = /([^&=]+)=?([^&]*)/g,
            decodeRE = /\+/g,  // Regex for replacing addition symbol with a space
            decode = function (str) {
                return decodeURIComponent(str.replace(decodeRE, " "));
            },
            params = {}, e;

        while (e = re.exec(query)) {
            var k = decode(e[1]), v = decode(e[2]);
            if (k.substring(k.length - 2) === '[]') {
                k = k.substring(0, k.length - 2);
                (params[k] || (params[k] = [])).push(v);
            }
            else params[k] = v;
        }
        return params;
    };

    /**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - option-type (integer, 0-3)
     *  - option-label (string)
     *  - option-tooltip-thumb
     *  - option-tooltip-value
     */
    $.widget('custom.SwatchRendererTooltip', {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltip_class: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltip_class),
                timer,
                type = parseInt($this.attr('option-type'), 10),
                label = $this.attr('option-label'),
                thumb = $this.attr('option-tooltip-thumb'),
                value = $this.attr('option-tooltip-value');

            if ($element.size() == 0) {
                $element = $('<div class="' + $widget.options.tooltip_class + '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>');
                $('body').append($element);
            }

            var $image = $element.find('.image'),
                $title = $element.find('.title'),
                $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {

                            // Image
                            if (type == 2) {
                                $image.css({
                                    'background': 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial'
                                });
                                $image.show();
                            }

                            // Color
                            else if (type == 1) {
                                $image.css({background: value});
                                $image.show();
                            }

                            // Textual or Clear
                            else if (type == 0 || type == 3) {
                                $image.hide();
                            }

                            $title.text(label);

                            var leftOpt = $this.offset().left,
                                left = leftOpt + ($this.width() / 2) - ($element.width() / 2),
                                $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            var leftCorner = 0;
                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + ($this.width() / 2) - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });
            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });

    /**
     * Render swatch controls with options and use tooltips.
     * Required two json:
     *  - jsonConfig (magento's option config)
     *  - jsonSwatchConfig (swatch's option config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - onlySwatches (hide selectboxes)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */
    $.widget('custom.SwatchRenderer', $.mage.SwatchRenderer, {
    	options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},

            // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProduct: '.product-info-main',

            // selector of price wrapper (need to know where set price)
            selectorProductPrice: '[data-role=priceBox]',

            //selector of product images gallery wrapper
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder-q]',

            // selector of category product tile wrapper
            selectorProductTile: '.product-item',

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // control label id
            controlLabelId: '',

            // text for more button
            moreButtonText: 'More',

            // Callback url for media
            mediaCallback: '',

            // Local media cache
            mediaCache: {},

            // Cache for BaseProduct images. Needed when option unset
            mediaGalleryInitial: [{}],

            // Use ajax to get image data
            useAjax: false,

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',

            // whether swatches are rendered in product list or on product page
            inProductList: false,

            // sly-old-price block selector
            slyOldPriceSelector: '.sly-old-price',

            // tier prise selectors start
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: '',
            // tier prise selectors end

            // A price label selector
            normalPriceLabelSelector: '.normal-price .price-label'
        },

        //==== magento <= 2.2.3 ====
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder-q]', '.qs-main'),
                isProductViewExist = $('body.cdz-qs-view').size() > 0,
                $main = isProductViewExist ?
                    this.element.parents('.qs-main') :
                    this.element.parents('.product-item-info');

            if (isProductViewExist) {
                gallery.on('gallery:loaded', function () {
                    var galleryObject = gallery.data('gallery');

                    options.mediaGalleryInitial = galleryObject.returnCurrentImages();
                });
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }
        },

        //==== magento 2.2.4 ====
        _loadMedia: function (eventName) {
            var $main = this.element.parents('.qs-main'),
                images;

            if (this.options.useAjax) {
                this._debouncedLoadProductMedia();
            }  else {
                images = this.options.jsonConfig.images[this.getProduct()];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }
                this.updateBaseImage(images, $main, !this.inProductList, eventName);
            }
        }
    });
});