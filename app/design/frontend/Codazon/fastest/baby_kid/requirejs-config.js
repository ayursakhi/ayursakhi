var config = {
  map: {
        "*": {
            "cdz_slider": "js/owlcarousel/owlslider",
            "modal" : "Magento_Ui/js/modal/modal",
			"cdz_menu": "js/menu/cdz_menu",
			"cdz_ajax_product":"Codazon_ProductFilter/js/ajaxload",
			"cdzZoom": "Magento_Catalog/js/cdzZoom"
        }
    },
    paths:  {
        "owlslider" : "js/owlcarousel/owl.carousel.min"    
    },
    "shim": {
		"js/owlcarousel/owl.carousel.min": ["jquery"],
        "Codazon_ProductFilter/js/product-gallery": ["jquery/ui"]
	},
	deps: [
        "Magento_Theme/js/fastest",
        "Codazon_ProductFilter/js/product-gallery"
    ]
  
};