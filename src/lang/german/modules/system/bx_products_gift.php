<?php
/**
 * $Id: /lang/german/modules/system/bx_products_gift.php 1000 2026-08-12 12:00:00Z benax $
 *
 * Deutsche Sprachkonstanten fuer das Systemmodul BX Products Gift.
 *
 * Diese Datei definiert Titel, Beschreibungen und Hinweistexte fuer die
 * Darstellung des Moduls in der Modified-Administration. Der Beschreibungstext
 * wird dynamisch aufgebaut, damit die Option zum Löschen der Moduldateien nur
 * dann angezeigt wird, wenn das Modul bereits deinstalliert ist.
 *
 * @package    Modified
 * @subpackage Language\German
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
  <h3>Produktzugaben</h3>
  <p>Dieses Modul ermöglicht die Konfiguration von Produktzugaben, die ab einem festgelegten Bestellwert hinzugefügt werden können.</p>';
  
  // Die physische Dateilöschung wird erst nach der Deinstallation angeboten.
  if((!defined('MODULE_BX_PRODUCTS_GIFT_STATUS')) || (MODULE_BX_PRODUCTS_GIFT_STATUS != 'True') && basename($_SERVER['PHP_SELF']) == 'module_export.php') {
    $module_description .= '<p><a class="button btnbox but_red" style="text-align:center;" onclick="return confirmLink(\'Alle Moduldateien löschen?\', \'\' ,this);" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=bx_products_gift&action=custom&delete=true') . '">Alle Moduldateien löschen</a></p>';
  }
  $module_description .= '</div></details>';
  
  define('MODULE_BX_PRODUCTS_GIFT_DESC', $module_description);

  define('MODULE_BX_PRODUCTS_GIFT_VERSION_TITLE', 'Version');
  define('MODULE_BX_PRODUCTS_GIFT_VERSION_DESC', 'Version des Moduls');

  define('MODULE_BX_PRODUCTS_GIFT_STATUS_TITLE', 'Status');
  define('MODULE_BX_PRODUCTS_GIFT_STATUS_DESC', 'Status des Moduls');

  define('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID_TITLE', 'Konfigurations-ID');
  define('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID_DESC', 'ID der Konfigurationsgruppe für das Modul');

  define('MODULE_BX_PRODUCTS_GIFT_SORT_ORDER_TITLE', 'Sortierreihenfolge');
  define('MODULE_BX_PRODUCTS_GIFT_SORT_ORDER_DESC', 'Sortierreihenfolge des Moduls');

  define('MODULE_BX_PRODUCTS_GIFT_TEXT_UNINSTALL_FIRST', 'Bitte das Modul zuerst deinstallieren.');

  define('MODULE_BX_PRODUCTS_GIFT_TEXT_COULD_NOT_BE_DELETED', 'Die Moduldateien konnten nicht gelöscht werden. Bitte die Dateiberechtigungen überprüfen.');
  define('MODULE_BX_PRODUCTS_GIFT_TEXT_SUCCESSFULLY_REMOVED', 'Das Modul wurde erfolgreich entfernt.');
  define('MODULE_BX_PRODUCTS_GIFT_TEXT_REMOVAL_INCOMPLETE', 'Die Entfernung des Moduls war unvollständig.');
  