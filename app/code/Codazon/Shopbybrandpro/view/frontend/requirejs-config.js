/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            "codazon_shopbybrand": 'Codazon_Shopbybrandpro/js/brands',
			"owlslider": 'Codazon_Shopbybrandpro/js/owl.carousel.min',
			"light_slider": 'Codazon_Shopbybrandpro/js/lightslider.min',
        }
    },
	shim:{
		"Codazon_Shopbybrandpro/js/owl.carousel.min": ["jquery"],
		"Codazon_Shopbybrandpro/js/brands": ["owlslider"],
		"Codazon_Shopbybrandpro/js/lightslider.min": ["jquery"]
	}
};
