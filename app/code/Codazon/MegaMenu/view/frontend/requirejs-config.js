/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            megamenu: 'Codazon_MegaMenu/js/menu',
			cdz_googlemap: 'Codazon_MegaMenu/js/googlemap',
        }
    },
	shim:{
		"Codazon_MegaMenu/js/menu": ["jquery"],
		"Codazon_MegaMenu/js/googlemap": ["jquery"]
	}
};
