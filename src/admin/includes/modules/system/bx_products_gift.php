<?php
/**
 * $Id: admin/includes/modules/system/bx_products_gift.php 1000 2026-08-12 13:00:00Z benax $
 *
 * Systemmodul für die Verwaltung des Moduls BX Products Gift innerhalb der Modified-Administration.
 *
 * @package    Modified
 * @subpackage Admin
 * @author     benax
 * @copyright  2009-2026 modified eCommerce Shopsoftware
 * @license    GNU General Public License (GPL)
 * @since      1.0.0
 */

	defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
	/**
	 * Verwaltet Installation, Konfiguration und Dateiaufräumung des Moduls.
	 */
  class bx_products_gift {
		public string $code;
		public string $title;
		public string $description;
		public int $sort_order;
		public bool $enabled;
		private bool $_check;
		public string $development_status; // 'p' = production ready, 'd' = in development, 'r' = draft

		public function __construct() {
			$this->code        = 'bx_products_gift';
			$this->title       = MODULE_BX_PRODUCTS_GIFT_TITLE;
			$this->description = MODULE_BX_PRODUCTS_GIFT_DESC;
			$this->sort_order  = defined('MODULE_BX_PRODUCTS_GIFT_SORT_ORDER') ? MODULE_BX_PRODUCTS_GIFT_SORT_ORDER : 0;
			$this->enabled     = ((defined('MODULE_BX_PRODUCTS_GIFT_STATUS') && MODULE_BX_PRODUCTS_GIFT_STATUS == 'True') ? true : false);
			$this->development_status = '';
		}

		/**
		 * Gibt zurück, ob das Modul installiert ist.
		 * @return bool
		 * 
		 * */
		public function check(): bool {
			if (!isset($this->_check)) {
				if (defined('MODULE_BX_PRODUCTS_GIFT_STATUS')) {
					$this->_check = true;
				} else {
					$check_query = xtc_db_query("SELECT configuration_value 
																				FROM " . TABLE_CONFIGURATION . " 
																				WHERE configuration_key = 'MODULE_BX_PRODUCTS_GIFT_STATUS'");
					$this->_check = xtc_db_num_rows($check_query);
				}
			}
			return $this->_check;
		}

		/**
			* Actions performed when the user clicks the install button.
			*
			* @return void
			*/
		public function install(): void {
			xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD ".$this->code." INTEGER(1) DEFAULT 0");
			xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$this->code." = 1");

			$freeId_query = xtc_db_query("SELECT (configuration_group_id+1) AS id 
																		FROM " . TABLE_CONFIGURATION_GROUP . " 
																		WHERE (configuration_group_id+1) NOT IN (SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id IS NOT NULL) 
																		LIMIT 1;");
			$freeId = xtc_db_fetch_array($freeId_query);

			$freeSort_query = xtc_db_query("SELECT (sort_order+1) AS sort_order 
																			FROM ".TABLE_CONFIGURATION_GROUP." 
																			WHERE (sort_order+1) NOT IN (SELECT sort_order FROM ".TABLE_CONFIGURATION_GROUP." WHERE sort_order IS NOT NULL) 
																			LIMIT 1;");
			$freeSort = xtc_db_fetch_array($freeSort_query);


			xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION_GROUP." ( configuration_group_id, 
																	configuration_group_title, 
																	configuration_group_description, 
																	sort_order, 
																	visible) 
															VALUES ( ".$freeId["id"].", 
																	'BX Products Gift', 
																	'Modul einstellen und konfigurieren',
																	".$freeSort["sort_order"].", 
																	1)");

			$query = "INSERT INTO ".TABLE_CONFIGURATION." ( 
															configuration_key, 
															configuration_value, 
															configuration_group_id, 
															sort_order, 
															date_added, 
															use_function, 
															set_function )
										VALUES ('MODULE_BX_PRODUCTS_GIFT_STATUS', 'True', '".$freeId["id"]."', '1', NOW(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'), '),
														('MODULE_BX_PRODUCTS_GIFT_VERSION', '1.0.0', '".$freeId["id"]."', '2', NOW(), '', ''),
														('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID', '".$freeId["id"]."', '".$freeId["id"]."', '3', NOW(), '', 'bx_configuration_field_version(')";
			xtc_db_query($query);

			xtc_db_query("CREATE TABLE IF NOT EXISTS bx_products_gift (
				products_gift_id INT(11) NOT NULL AUTO_INCREMENT,
				products_id VARCHAR(255) NOT NULL,
				products_gift_sum DECIMAL(14,2) NOT NULL DEFAULT '0.00',
				customers_groups VARCHAR(100) NOT NULL DEFAULT '',
				PRIMARY KEY (products_gift_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
			
		}

		public function update(): void {}
			
		/**
			* Actions performed when the user clicks the uninstall button.
			*
			* @return void
			*/			
		public function remove(): void {
			$groupId = $this->getConfigurationGroupId();

			xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key in ('".implode("', '", $this->keys())."')");

			if ($groupId > 0) {
				xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION_GROUP." WHERE configuration_group_id = ".(int)$groupId);
			}
			
			xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP ".$this->code);

			xtc_db_query("DROP TABLE IF EXISTS bx_products_gift");
		}

		/**
			* Configuration keys used by the module. Used when installing and removing the module.
			*
			* @return array
			*/
		public function keys(): array {
			$keys = array('MODULE_BX_PRODUCTS_GIFT_STATUS',
										'MODULE_BX_PRODUCTS_GIFT_VERSION',
										'MODULE_BX_PRODUCTS_GIFT_CONFIG_ID',
									);
			return $keys;
		}

		public function process(): void { }
			
		/**
			* Additional HTML to show during module configuration.
			*
			* @return array
			*/			
		public function display(): array {
				return array('text' => '<div style="text-align: center;">'.xtc_button(BUTTON_SAVE).xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set='.$_GET['set'].'&module='.$this->code))."</div>");
		}
			
		public function custom(): void {
			global $messageStack;

			// Moduldateien dürfen erst entfernt werden, nachdem das Modul logisch
			// aus dem System abgemeldet wurde.
			if ($this->check()) {
				$messageStack->add_session(MODULE_BX_PRODUCTS_GIFT_TEXT_UNINSTALL_FIRST, 'error');
				return;
			}

			$delete = (isset($_GET['delete']) && $_GET['delete'] === 'true');

			if ($delete !== true) {
				return;
			}

			$result = true;
				
			// Diese Liste enthält die in der Live-Installation ausgerollten Dateien.
			$dirs_and_files   = array();
			$dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'bx_products_gift.php';
			//$dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'includes/extra/css/bx_products_gift.php';
			//$dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'includes/extra/filenames/bx_products_gift.php';
			//$dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'includes/extra/menu/bx_products_gift.php';
			//$dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'includes/extra/modules/orders/orders_print/bx_products_gift.php';
			$dirs_and_files[] = DIR_FS_CATALOG.DIR_ADMIN.'images/icons/heading/bx_products_gift.png';
			
			$dirs_and_files[] = DIR_FS_CATALOG.'lang/german/modules/system/bx_products_gift.php';
			$dirs_and_files[] = DIR_FS_CATALOG.'lang/english/modules/system/bx_products_gift.php';
			$dirs_and_files[] = DIR_FS_CATALOG.'lang/german/admin/bx_products_gift.php';
			$dirs_and_files[] = DIR_FS_CATALOG.'lang/english/admin/bx_products_gift.php';
				
			// Dateien löschen
			foreach ($dirs_and_files as $dir_or_file) {
				if (!$this->secureDelete($dir_or_file)) {
					$messageStack->add_session(MODULE_BX_PRODUCTS_GIFT_TEXT_COULD_NOT_BE_DELETED.' '.$dir_or_file, 'error');
					$result = false;
				}
			}
				
			if ($result === true) {
				$messageStack->add_session(MODULE_BX_PRODUCTS_GIFT_TEXT_SUCCESSFULLY_REMOVED, 'success');
			} else {
				$messageStack->add_session(MODULE_BX_PRODUCTS_GIFT_TEXT_REMOVAL_INCOMPLETE, 'error');
			}
				
			// Datei selbst löschen
			$this->secureDelete(DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/system/bx_products_gift.php');

			xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system'));
		}

		private function secureDelete(string $path): bool {
			// 1. Existiert der Pfad überhaupt?
			if (!file_exists($path)) {
				return true;
			}

			// --- SICHERHEITS-CHECK ---
			// Holt den echten, bereinigten Pfad (löst relative Teile auf)
			$realPath  = realpath($path);
			$rootPath = realpath(DIR_FS_CATALOG);

			// Sicherheitsregel A: Pfad darf nicht leer sein
			if (empty($realPath) || empty($rootPath)) {
				return false;
			}

			// Sicherheitsregel B: Wenn der Pfad EXAKT dein Admin-Hauptordner 
			// oder das Hauptverzeichnis (/) ist -> SOFORT ABBRECHEN!
			if ($realPath === $rootPath || $realPath === DIRECTORY_SEPARATOR) {
				return false;
			}

			// Sicherheitsregel C: Der Pfad muss unterhalb von $rootPath liegen.
			if (strpos($realPath, $rootPath . DIRECTORY_SEPARATOR) !== 0) {
				return false;
			}
			// -----------------------------------

			if (!is_writable($realPath)) {
				return false;
			}

			// Wenn es eine Datei oder ein Symlink ist -> nur diese löschen und beenden!
			if (!is_dir($realPath) || is_link($realPath)) {
				return unlink($realPath);
			}

			// Nur wenn es ein Ordner ist, wird tiefer gegangen
			// Eigene Rekursion statt RecursiveDirectoryIterator: so entscheiden wir
			// selbst, dass Symlinks auf Verzeichnisse NICHT verfolgt werden, und
			// prüfen bei jedem einzelnen Element erneut, ob wir noch innerhalb von
			// $rootPath sind (Schutz vor präparierten Symlinks im Verzeichnisbaum).
			if (!$this->deleteDirectoryContents($realPath, $rootPath)) {
				return false;
			}

			return rmdir($realPath);
		}

		/**
		 * Recursively deletes the contents of a directory without following
		 * symlinked subdirectories, verifying that every item stays within
		 * $rootPath before it is touched.
		 *
		 * @param string $dir       Real, resolved path of the directory to empty.
		 * @param string $rootPath Real, resolved path of the allowed root.
		 *
		 * @return bool
		 */
		private function deleteDirectoryContents(string $dir, string $rootPath): bool {
			$entries = scandir($dir);
			if ($entries === false) {
				return false;
			}

			foreach ($entries as $entry) {
				if ($entry === '.' || $entry === '..') {
					continue;
				}

				$itemPath = $dir . DIRECTORY_SEPARATOR . $entry;

				// Symlinks NIEMALS verfolgen - nur den Link selbst entfernen, nie das Ziel.
				if (is_link($itemPath)) {
					if (!unlink($itemPath)) {
						return false;
					}
					continue;
				}

				// Defense in depth: bei jedem Element erneut sicherstellen, dass der
				// aufgelöste Pfad noch innerhalb von $rootPath liegt.
				$realItemPath = realpath($itemPath);
				if ($realItemPath === false || strpos($realItemPath, $rootPath . DIRECTORY_SEPARATOR) !== 0) {
					return false;
				}

				if (!is_writable($realItemPath)) {
					return false;
				}

				if (is_dir($realItemPath)) {
					if (!$this->deleteDirectoryContents($realItemPath, $rootPath)) {
						return false;
					}
					if (!rmdir($realItemPath)) {
						return false;
					}
				} else {
					if (!unlink($realItemPath)) {
						return false;
					}
				}
			}

			return true;
		}

		public function getConfigurationGroupId(): int {
			if (defined('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID')) {
				return (int)constant('MODULE_BX_PRODUCTS_GIFT_CONFIG_ID');
			}

			// Nach einer Teil-Deinstallation kann die Gruppen-ID nur noch aus der
			// Konfigurationstabelle rekonstruiert werden.
			$result_query = xtc_db_query("SELECT configuration_value AS value
																			FROM ".TABLE_CONFIGURATION."
																		WHERE configuration_key = 'MODULE_BX_PRODUCTS_GIFT_CONFIG_ID'");
			if (xtc_db_num_rows($result_query) > 0) {
				$result = xtc_db_fetch_array($result_query);
				return (int)$result['value'];
			}

			return 0;
		}
}
  