<?php
/**
 * $ $Id: bx_products_gift.php 2026-8-12 12:00:00Z BENAX $
 * 
 * Proprietäres Modul: BX Products Gift
 *
 * Diese Datei enthält proprietäre Erweiterungen und darf ohne
 * ausdrückliche schriftliche Genehmigung nicht vervielfältigt,
 * weitergegeben oder außerhalb des lizenzierten Projekts genutzt werden.
 *
 * @package    Modified\Admin
 * @subpackage BX_Products_Gift
 * @version    1.0.0
 * @since      2026-03-04
 */

  require ('includes/application_top.php');

  require_once(DIR_WS_CLASSES.'categories.php');
  require_once(DIR_FS_INC.'xtc_get_tax_rate.inc.php');
  require_once(DIR_FS_INC.'xtc_datetime_short.inc.php');
  
  // Währungssymbol mit NumberFormatter ermitteln
  $currency_symbol = '&euro;'; // Fallback
  if (class_exists('NumberFormatter')) {
    $formatter = new NumberFormatter(DATE_LOCALE, NumberFormatter::CURRENCY);
    $currency_symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
  }

  $actionWhitelist = array('delete', 'update', 'updateOrginal', 'new', 'load_products', 'get_gift_table');

  $action = (isset($_GET['action']) && in_array($_GET['action'], $actionWhitelist)) ? $_GET['action'] : '';
  
  switch ($action) {
    case 'load_products':
      header('Content-Type: application/json; charset=utf-8');

      $category_id = isset($_GET['catID']) ? (int)$_GET['catID'] : 0;
      $products_response = array();

      if ($category_id > 0) {
        $products_query = xtc_db_query(
          "SELECT DISTINCT
                    p.products_id,
                    p.products_price,
                    p.products_tax_class_id,
                    pd.products_name
             FROM " . TABLE_PRODUCTS . " p
             INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                     ON pd.products_id = p.products_id
             INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                     ON p2c.products_id = p.products_id
             LEFT JOIN " . TABLE_BX_PRODUCTS_GIFT . " pg
                    ON pg.products_id = p.products_id
            WHERE p2c.categories_id = " . $category_id . "
              AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
              AND pg.products_id IS NULL
            ORDER BY pd.products_name"
        );

        while ($product = xtc_db_fetch_array($products_query, true)) {
          $price_gross = $product['products_price']
            * (xtc_get_tax_rate($product['products_tax_class_id']) / 100)
            + $product['products_price'];

          $products_response[] = array(
            'id' => (int)$product['products_id'],
            'text' => (int)$product['products_id'] . ': '
              . $product['products_name'] . ' - '
              . round($price_gross, PRICE_PRECISION),
          );
        }
      }

      echo json_encode(array(
        'success' => true,
        'products' => $products_response,
      ), JSON_UNESCAPED_UNICODE);
      exit;

    case 'delete':
      $gift_id = (int)($_POST['id'] ?? 0);
      if ($gift_id <= 0) {
        http_response_code(400);
        exit('Invalid gift ID');
      }

      xtc_db_query("DELETE FROM ".TABLE_BX_PRODUCTS_GIFT." WHERE products_gift_id = '".$gift_id."'");

      if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        exit('success');
      }

			xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
    break;

    case 'update':
      $gift_id = (int)$_POST['save_id'];
      $sum = str_replace(',', '.', $_POST['products_gift_sum'] ?? '');
      if (!is_numeric($sum)) {
        exit('invalid value');
      }
      $sum = xtc_db_prepare_input($sum);

      xtc_db_query("UPDATE ".TABLE_BX_PRODUCTS_GIFT." SET 
        products_gift_sum      = '".$sum."' 
        WHERE products_gift_id = '".$gift_id."'");

      // Wenn der Aufruf per AJAX kam, kein Redirect ausführen
      if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        exit('success');
      }

      xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
    break;
    
    case 'new':
      $products_id = (int)($_POST['products_gift'] ?? 0);
      $category_id = (int)($_POST['catID'] ?? 0);

      $product_check_query = xtc_db_query(
        "SELECT p.products_id
           FROM " . TABLE_PRODUCTS . " p
           INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                   ON p2c.products_id = p.products_id
           LEFT JOIN " . TABLE_BX_PRODUCTS_GIFT . " pg
                  ON pg.products_id = p.products_id
          WHERE p.products_id = " . $products_id . "
            AND p2c.categories_id = " . $category_id . "
            AND pg.products_id IS NULL
          LIMIT 1"
      );

      if ($products_id <= 0 || $category_id <= 0 || xtc_db_num_rows($product_check_query) === 0) {
        $messageStack->add_session(BX_ERROR_INVALID_PRODUCT_SELECTION, 'error');
        xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
        exit;
      }

			// Kundengruppen einlesen (Checkbox-Feld fehlt im POST, wenn keine Gruppe angehakt wurde)
			$groups_post = (isset($_POST['groups']) && is_array($_POST['groups'])) ? $_POST['groups'] : array();
			$groups_anz  = count($groups_post);
			$groups_list = '';
			
			// Beim letzten element, keine Kommata
			for($i = 0; $i < $groups_anz; $i++){
				$groups_list .= $groups_post[$i].($i+1 != $groups_anz ? ',' : '');
			}
			
			$products_gift_array = array(
        'products_id' 			=> $products_id,
				'products_gift_sum' => xtc_db_prepare_input($_POST['products_gift_sum']),
				'customers_groups' 	=> xtc_db_prepare_input($groups_list),
        'created_at'        => 'now()'
      );
							
			xtc_db_perform(TABLE_BX_PRODUCTS_GIFT, $products_gift_array);
			xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
		break;
    case 'get_gift_table':
      header('Content-Type: application/json; charset=utf-8');

      $products_gift_table = "SELECT 
                                pg.products_gift_id, 
                                pg.products_gift_sum,
                                pg.customers_groups,
                                pg.created_at,
                                p.products_tax_class_id,
                                pd.products_name
                              FROM ".TABLE_BX_PRODUCTS_GIFT." pg
                              JOIN ".TABLE_PRODUCTS." p 
                                ON pg.products_id = p.products_id
                              LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                ON p.products_id = pd.products_id
                                AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                              ORDER BY pg.products_gift_id ASC";

      $products_gift_table_query = xtc_db_query($products_gift_table);
      $data = array();
      $customer_group_names = array();
      $customer_group_query = xtc_db_query(
        "SELECT customers_status_id, customers_status_name
           FROM " . TABLE_CUSTOMERS_STATUS . "
          WHERE language_id = " . (int)$_SESSION['languages_id']
      );

      while ($customer_group = xtc_db_fetch_array($customer_group_query, true)) {
        $customer_group_names[(int)$customer_group['customers_status_id']] = $customer_group['customers_status_name'];
      }

      while($products_gift = xtc_db_fetch_array($products_gift_table_query)) {
        $name = empty($products_gift['products_name']) ? BX_TEXT_TRANSLATION_MISSING : $products_gift['products_name'];
        
        // Kundengruppen-Namen aufbereiten
        $group_list       = explode(',', $products_gift['customers_groups']);
        $group_list_count = count($group_list);

        for($a = 0; $a < $group_list_count; $a++) {
          $group_id = trim($group_list[$a]);
          $group_list[$a] = $group_id === 'all'
            ? TXT_ALL
            : ($customer_group_names[(int)$group_id] ?? '');
        }

        $tax_rate = (float)xtc_get_tax_rate($products_gift['products_tax_class_id']);

        $data[] = array(
          'id'            => (int)$products_gift['products_gift_id'],
          'name'          => $name,
          'groups'        => implode(', ', $group_list),
          'sum'           => $products_gift['products_gift_sum'],
          'tax_rate'      => $tax_rate,
          'created_at'    => xtc_datetime_short($products_gift['created_at'])
        );
      }

      echo json_encode(array(
        'success' => true,
        'items'   => $data,
        'labels'  => array(
          'net'           => BX_TEXT_NET,
          'gross'         => BX_TEXT_GROSS,
          'enterNet'      => BX_TEXT_ENTER_NET,
          'enterGross'    => BX_TEXT_ENTER_GROSS,
          'confirmDelete' => BX_TEXT_PRODUCTS_GIFT_CONFIRM_DELETE,
          'delete'        => BX_TEXT_PRODUCTS_GIFT_DELETE,
        ),
      ), JSON_UNESCAPED_UNICODE);
      exit;  
    }
  
  require_once (DIR_WS_INCLUDES.'head.php');

  $messageStack->output();
?>
</head>
<!-- header //-->
<?php require(DIR_WS_INCLUDES.'header.php'); ?>

<!-- header_eof //-->
<!-- body //-->
<table class="tableBody">
  <tr>
    <?php //left_navigation
    if (USE_ADMIN_TOP_MENU == 'false') {
      echo '<td class="columnLeft2">'.PHP_EOL;
      echo '<!-- left_navigation //-->'.PHP_EOL;
      require_once(DIR_WS_INCLUDES.'column_left.php');
      echo '<!-- left_navigation eof //-->'.PHP_EOL;
      echo '</td>'.PHP_EOL;
    }
    ?>
    <!-- body_text //-->
    <td class="boxCenter">
      <div class="pageHeadingImage">
        <?php echo xtc_image(DIR_WS_ICONS.'heading/bx_products_gift.png', MODULE_PRODUCTS_GIFT_PAGE_TITLE, '', '', 'style="max-height: 32px;"'); ?>
      </div>
      <div class="pageHeading flt-l">
        <?php echo MODULE_PRODUCTS_GIFT_PAGE_TITLE; ?>
        <div class="main pdg2">
          <?php echo MODULE_PRODUCTS_GIFT_PAGE_SUBTITLE; ?>
        </div>
      </div>
      <div class="clear"></div>

      <table class="tableCenter" style="margin-top: 5px;">
        <tr>
          <td class="boxCenterLeft">
            <div class="main" style="display: flex; flex-direction: row; justify-content: left; align-items: center; background: #AF417E; color: #ffffff; border-radius: 4px; margin: 0 0 5px 0; padding: 4px 0 2px 0;">
              <div class="main" style="margin: 5px 10px;"><strong><?php echo MODULE_PRODUCTS_GIFT_BANNER_TITLE; ?></strong></div>
              <div class="main" style="margin: 5px 10px;"><?php echo MODULE_PRODUCTS_GIFT_BANNER_SUBTITLE; ?></div>
            </div>
            
            <div class="boxCenter">
              <div class="clear">
              <?php
                $products_array    = array(array('id' => '', 'text' => BX_TEXT_SELECT_CATEGORY));
                $gift_sum_tax_rate = xtc_get_tax_rate('1');

                echo xtc_draw_form('products_gift', FILENAME_BX_PRODUCTS_GIFT, 'action=new', 'post','');
                ?>
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableRow">
                      <td class="dataTableContent" style="vertical-align: top !important;">
                        <p class="column-label"><?php echo BX_TEXT_PRODUCTS_NAME; ?></p>
                      </td>
                      <td class="dataTableContent">

                        <div class="gift-select-row">
                          <div class="gift-select-column">
                            <p class="column-label">Kategorie</p>
                            <?php echo xtc_draw_pull_down_menu('catID', xtc_get_category_tree('0'), ( isset($_GET['catID']) ? $_GET['catID'] : 0 ), ''); ?>
                          </div>
                          <div class="gift-select-column">
                            <p class="column-label">Produkt</p>
                            <?php echo xtc_draw_pull_down_menu('products_gift', $products_array); ?>
                          </div>                          
                          <div class="gift-select-column">
                            <p class="column-label"><?php echo BX_TEXT_PRODUCTS_GIFT_SUM; ?></p>
                            <div class="gift-select-subrow">
                              <div style="min-width: 50px;">
                                <?php echo xtc_draw_input_field('products_gift_sum', '', 'id="gift_sum_netto_0" data-tax-rate="' . (float)$gift_sum_tax_rate . '" placeholder="'.BX_TEXT_ENTER_NET.'"'); ?>
                              </div>
                              <div style="min-width: 50px;">
                                <?php echo xtc_draw_input_field('dummy', '', 'id="gift_sum_brutto_0" data-tax-rate="' . (float)$gift_sum_tax_rate . '" placeholder="'.BX_TEXT_ENTER_GROSS.'"'); ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="dataTableContent">
                        &nbsp;
                      </td>
                    </tr>
                    <tr>
                      <td class="dataTableContent" style="vertical-align: top !important;">
                        <strong><?php echo BX_TEXT_PRODUCTS_GIFT_GROUP; ?></strong>
                      </td>
                      <td class="dataTableContent">
                        <div class="main gift-customer-groups">
                        <?php
                          if (GROUP_CHECK == 'true') {
                              $giftInfo = new stdClass();
                              $customers_status_query = xtc_db_query("SELECT customers_status_id FROM " . TABLE_CUSTOMERS_STATUS . " WHERE language_id = '".(int)$_SESSION['languages_id']."'");

                              while ($customers_status = xtc_db_fetch_array($customers_status_query, true)) {
                                $giftInfo->{'group_permission_'.$customers_status['customers_status_id']} = 0;
                              }

                              $catfunc = new categories();
                              // "Alle"-Checkbox ist in categories.php immer vorbelegt, wenn pID/cID fehlen -> hier entfernen
                              $permission_checkboxes = str_replace('checked="checked" id="cgAll"', 'id="cgAll"', $catfunc->create_permission_checkboxes($giftInfo));
                              $permission_checkboxes = preg_replace('/<br\s*\/?\s*>/i', ' 🔸 ', $permission_checkboxes);
                              $permission_checkboxes = preg_replace('/\s*🔸\s*$/u', '', $permission_checkboxes);
                              echo $permission_checkboxes;
                          }
                        ?>
                        </div>
                      </td>
                      <td class="dataTableContent">
                        <?php echo '<input type="submit" class="button" style="margin: 0;" onclick="this.blur(); return bxValidateGiftGroups(this.form);" value="' . BUTTON_SAVE . '"/>'; ?>
                      </td>
                    </tr>
                  </table>
                </form>
              </div>
              <br>
              <table class="tableBoxCenter collapse">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent txta-c" style="width: 5%;"><?php echo BX_TEXT_PRODUCTS_GIFT_ID; ?></td>
                    <td class="dataTableHeadingContent" style="width: 40%;"><?php echo BX_TEXT_PRODUCTS_NAME; ?></td>
                    <td class="dataTableHeadingContent" style="width: 30%;"><?php echo BX_TEXT_PRODUCTS_GIFT_SUM; ?></td>
                    <td class="dataTableHeadingContent" style="width: 15%;"><?php echo BX_TEXT_PRODUCTS_GIFT_CREATED_AT; ?></td>
                    <td class="dataTableHeadingContent txta-c" style="width: 10%;"><?php echo BX_TEXT_PRODUCTS_GIFT_ACTION; ?></td>
                  </tr>
                </thead>
                <tbody id="bx-gift-table-body">
                  <!-- Zeilen werden per AJAX / Staggered Rendering eingefügt -->
                </tbody>
              </table>
            </div>
          </td>
          <td class="boxRight">
<?php

  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>'.BX_HEADING_RIGHT.'</strong>');
  $contents[] = array('text' => BX_CONTENT_RIGHT. ' '.$currency_symbol);

  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
  }
?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES.'footer.php'); ?>
<!-- footer_eof //-->
<script>
	$(document).ready(function() {
		$(".fixed_messageStack").slideDown("slow", function() {
			setTimeout(function() { $(".fixed_messageStack").slideUp("slow"); }, 2000); 
		});
	});

	// Verhindert das Absenden, wenn im "Neu anlegen"-Formular keine Kundengruppe angehakt ist
	function bxValidateGiftGroups(form) {
		var boxes = form.querySelectorAll('input[name="groups[]"]');
		if (boxes.length === 0) {
			return true;
		}
		var checked = form.querySelectorAll('input[name="groups[]"]:checked');
		if (checked.length === 0) {
      alert(<?php echo json_encode(BX_TEXT_SELECT_CUSTOMER_GROUP); ?>);
			return false;
		}
		return true;
	}
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES.'application_bottom.php'); ?>
