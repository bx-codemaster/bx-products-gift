<?php
/**
 * $Id: /lang/english/modules/system/bx_products_gift.php 1000 2026-08-12 12:00:00Z benax $
 *
 * English language constants for the system module BX Products Gift.
 *
 * This file defines titles, descriptions, and notes for the
 * display of the module in the Modified administration. The description text
 * is built dynamically so that the option to delete the module files is only
 * displayed when the module has already been uninstalled.
 *
 * @package    Modified
 * @subpackage Language\English
 * @author     benax
 * @copyright  2009-2026 modified eCommerce Shopsoftware
 * @license    GNU General Public License (GPL)
 * @since      1.0.0
 */
	
  define('MODULE_BX_PRODUCTS_GIFT_TITLE', 'BX Products Gift');
  
  $module_description = '<details class="bxac-card">
<summary class="bxac-summary" style="list-style: none;">
  <span class="bxac-arrow">▸</span>
  <span class="bxac-title">'
  .xtc_image(DIR_WS_ICONS.'heading/bx_products_gift.png', 'BX Products Gift', '', '', 'style="max-height: 32px; margin-right: 8px;"')
  .' BX Products Gift
  </span>
</summary>
<div class="bxac-body">
  <p>This module allows the configuration of product additions that can be added from a specified order value.</p>';
  
  // The physical deletion of files is only offered after the module has been uninstalled.
  if((!defined('MODULE_BX_PRODUCTS_GIFT_STATUS')) || (MODULE_BX_PRODUCTS_GIFT_STATUS != 'True') && basename($_SERVER['PHP_SELF']) == 'module_export.php') {
    $module_description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Delete all module files?\', \'\' ,this);" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_products_gift&action=custom&delete=true') . '">Delete all module files</a></p>';
  }
  $module_description .= '</div></details>';
  
  define('MODULE_BX_PRODUCTS_GIFT_DESC', $module_description);

  define('MODULE_BX_PRODUCTS_GIFT_VERSION_TITLE', 'Version');
  define('MODULE_BX_PRODUCTS_GIFT_VERSION_DESC', 'Version of the module');

  define('MODULE_BX_PRODUCTS_GIFT_STATUS_TITLE', 'Status');
  define('MODULE_BX_PRODUCTS_GIFT_STATUS_DESC', 'Status of the module');

  define('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID_TITLE', 'Configuration ID');
  define('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID_DESC', 'ID of the configuration group for the module');

  define('MODULE_BX_PRODUCTS_GIFT_SORT_ORDER_TITLE', 'Sort order');
  define('MODULE_BX_PRODUCTS_GIFT_SORT_ORDER_DESC', 'Sort order of the module');
  
  define('MODULE_BX_PRODUCTS_GIFT_TEXT_UNINSTALL_FIRST', 'Please uninstall the module first.');
  
  define('MODULE_BX_PRODUCTS_GIFT_TEXT_COULD_NOT_BE_DELETED', 'The module files could not be deleted. Please check the file permissions.');
  define('MODULE_BX_PRODUCTS_GIFT_TEXT_SUCCESSFULLY_REMOVED', 'The module was successfully removed.');
  define('MODULE_BX_PRODUCTS_GIFT_TEXT_REMOVAL_INCOMPLETE', 'The removal of the module was incomplete.');
  