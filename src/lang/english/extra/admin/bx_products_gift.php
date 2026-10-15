<?php
/* -----------------------------------------------------------------------------------------
	$Id: /lang/english/extra/admin/bx_products_gift.php 1000 2026-08-12 12:00:00Z benax $
	
	modified eCommerce Shopsoftware
	http://www.modified-shop.org
	
	Copyright (c) 2009 - 2013 [www.modified-shop.org]
	-----------------------------------------------------------------------------------------
	Released under the GNU General Public License
	---------------------------------------------------------------------------------------*/
	
  define('MODULE_PRODUCTS_GIFT_PAGE_TITLE', 'BX Products Gift');
  define('MODULE_PRODUCTS_GIFT_PAGE_SUBTITLE', 'Product gifts for customers');

	define('MODULE_PRODUCTS_GIFT_BANNER_TITLE', '🚀 BX Products Gift v'.(defined('MODULE_BX_PRODUCTS_GIFT_VERSION') ? MODULE_BX_PRODUCTS_GIFT_VERSION : ''));
	define('MODULE_PRODUCTS_GIFT_BANNER_SUBTITLE', 'Professional solution for product gifts');

	define('MODULE_TEXT_SAVE', 'Save settings');
	define('MODULE_TEXT_SAVE_SUCCESS', 'Settings saved successfully');

	define('BX_TEXT_PRODUCTS_NAME', 'Product name');
	define('BX_TEXT_TRANSLATION_MISSING_PLAIN', 'Translation missing');
	// Backward-compatible alias: earlier versions embedded raw HTML here.
	// Since the table is now built via AJAX/JSON, the text runs through the
	// client-side esc() function, which escapes HTML tags instead of rendering
	// them - embedded markup would now only show up as literal text.
	define('BX_TEXT_TRANSLATION_MISSING', BX_TEXT_TRANSLATION_MISSING_PLAIN);
	define('BX_TEXT_PRODUCTS_GIFT_SUM', 'Free on orders of:');
	define('BX_TEXT_PRODUCTS_GIFT_SUM_UPDATE', 'Update &quot;Free on ...&quot;');
	define('BX_TEXT_PRODUCTS_GIFT_GROUP', 'Visible to customer group');
	define('BX_TEXT_PRODUCTS_GIFT_ID', 'ID');
	define('BX_TEXT_PRODUCTS_GIFT_ACTION', 'Action');
	define('BX_TEXT_PRODUCTS_GIFT_CREATED_AT', 'Created at');
	define('BX_TEXT_GIFT_SCOPE', 'Eligibility');
	define('BX_TEXT_GIFT_SCOPE_GLOBAL', 'Entire cart');
	define('BX_TEXT_GIFT_SCOPE_CATEGORY', 'Category');
	define('BX_TEXT_GIFT_MATCH_MODE', 'Category condition');
	define('BX_TEXT_GIFT_MATCH_ANY', 'At least one item');
	define('BX_TEXT_GIFT_MATCH_ALL', 'All items');
	define('BX_TEXT_GIFT_PRODUCT_CATEGORY', 'Gift product category');

	define('BX_TEXT_PRODUCTS_GIFT_CONFIRM_DELETE', 'Are you sure you want to delete this product gift?');
	define('BX_TEXT_PRODUCTS_GIFT_DELETE', 'Delete');
	define('BX_TEXT_PRODUCTS_GIFT_SAVE', 'Save');

	define('BX_TEXT_ENTER_NET', 'Enter net amount...');
	define('BX_TEXT_ENTER_GROSS', 'Enter gross amount...');

	define('BX_TEXT_SELECT_CATEGORY', 'Please select a category first');
	define('BX_TEXT_LOADING_PRODUCTS', 'Loading products ...');
	define('BX_TEXT_NO_PRODUCTS_AVAILABLE', 'No products available');
	define('BX_TEXT_SELECT_PRODUCT', 'Please select a product');
	define('BX_TEXT_PRODUCTS_LOAD_FAILED', 'Products could not be loaded');
	define('BX_ERROR_INVALID_PRODUCT_SELECTION', 'Please select a valid category and an available product.');
	define('BX_TEXT_SELECT_CUSTOMER_GROUP', 'Please select at least one customer group.');

	define('BX_TEXT_NET', 'Net');
	define('BX_TEXT_GROSS', 'Gross');

	define('BX_HEADING_RIGHT', 'System messages');
	define('BX_CONTENT_RIGHT', 'System notifications');
