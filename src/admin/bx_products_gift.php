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

  $actionWhitelist = array('delete', 'update', 'updateOrginal', 'new');
  $action = (isset($_GET['action']) && in_array($_GET['action'], $actionWhitelist)) ? $_GET['action'] : '';
  switch ($action) {
    case 'delete': 
      xtc_db_query("DELETE FROM ".TABLE_BX_PRODUCTS_GIFT." WHERE products_gift_id = '".(int)$_GET['id']."'");
			xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
    break;

    case 'update':
      $gift_id = (int)$_POST['save_id'];
      $sum     = xtc_db_prepare_input($_POST['products_gift_sum']);

      xtc_db_query("UPDATE ".TABLE_BX_PRODUCTS_GIFT." SET 
        products_gift_sum      = '".$sum."' 
        WHERE products_gift_id = '".$gift_id."'");

      // Wenn der Aufruf per AJAX kam, kein Redirect ausführen
      if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        exit('success');
      }

      xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
    break;

		case 'updateOrginal':
      $products_gift_sum = xtc_db_prepare_input($_POST['products_gift_sum']);
			xtc_db_query("UPDATE ".TABLE_BX_PRODUCTS_GIFT." SET 
				products_gift_sum = '".$products_gift_sum."' 
				WHERE products_gift_id = '".(int)$_POST['products_gift_id']."'");
			xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
		break;
    
    case 'new':
			// Kundengruppen einlesen (Checkbox-Feld fehlt im POST, wenn keine Gruppe angehakt wurde)
			$groups_post = (isset($_POST['groups']) && is_array($_POST['groups'])) ? $_POST['groups'] : array();
			$groups_anz  = count($groups_post);
			$groups_list = '';
			
			// Beim letzten element, keine Kommata
			for($i = 0; $i < $groups_anz; $i++){
				$groups_list .= $groups_post[$i].($i+1 != $groups_anz ? ',' : '');
			}
			
			$products_gift_array = array(
				'products_id' 			=> xtc_db_prepare_input($_POST['products_gift']),
				'products_gift_sum' => xtc_db_prepare_input($_POST['products_gift_sum']),
				'customers_groups' 	=> xtc_db_prepare_input($groups_list) );
							
			xtc_db_perform(TABLE_BX_PRODUCTS_GIFT, $products_gift_array);
			xtc_redirect(xtc_href_link(FILENAME_BX_PRODUCTS_GIFT));
		break;

		default:
    // alle anzeigen
		// alle gratisartikel einlesen
/*
		$products_gift = "SELECT 
				p.products_id, 
				p.products_price,
				p.products_tax_class_id,
				pd.products_name
			FROM ".TABLE_PRODUCTS." p,
				".TABLE_PRODUCTS_DESCRIPTION." pd
			WHERE p.products_gift = '1'
			AND p.products_id = pd.products_id
			AND pd.language_id = '".(int)$_SESSION['languages_id']."'
			ORDER BY p.products_id ASC";
*/      
    $products_gift = "SELECT 
                        p.products_id,
                        p.products_price,
                        p.products_tax_class_id,
                        pd.products_name
                      FROM ".
                        TABLE_PRODUCTS." p, ".
                        TABLE_PRODUCTS_DESCRIPTION." pd,".
                        TABLE_PRODUCTS_TO_CATEGORIES." p2c
                      WHERE 
                        p.products_id = pd.products_id
                        AND p.products_id = p2c.products_id
                        AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                      ORDER BY pd.products_name";

		$products_gift_query = xtc_db_query($products_gift);
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
                // pulldown bilden
                $products_array[] = array('id' => '', 'text' => TEXT_SELECT);

                while($products_gift = xtc_db_fetch_array($products_gift_query, true)) {
                  // prüfen welche artikel bereits in der tabelle eingetragen sind und aus dem dropdown entfernen
                  $products_gift_table_true = " SELECT products_gift_id, 
                                                       products_id
                                                FROM " . TABLE_BX_PRODUCTS_GIFT . "
                                                WHERE products_id = '".$products_gift['products_id']."'";
                    
                  $products_gift_table_true_query = xtc_db_query($products_gift_table_true);
                  $products_gift_true             = xtc_db_fetch_array($products_gift_table_true_query);
                  
                  if(!$products_gift_true) {
                    // mwst. dazu rechnen
                    $price_mwst  = ($products_gift['products_price'] * (xtc_get_tax_rate($products_gift['products_tax_class_id']) / 100) + $products_gift['products_price']);
                    $price_round = round($price_mwst, PRICE_PRECISION);
                    
                    $products_array[] = array(
                      'id'   => $products_gift['products_id'],
                      'text' => $products_gift['products_id'].': '.$products_gift['products_name'].' - '.$price_round
                    );
                  }	
                }
                
                echo xtc_draw_form('products_gift', FILENAME_BX_PRODUCTS_GIFT, 'action=new', 'post','');
                ?>
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableRow">
                      <td class="dataTableContent">
                        <strong><?php echo BX_TEXT_PRODUCTS_NAME; ?></strong>
                      </td>
                      <td class="dataTableContent">
                        <?php echo bx_draw_pull_down_menu('products_gift', $products_array, '', 'style="width:300px;"'); ?>
                      </td>
                      <td class="dataTableContent">
                        <strong><?php echo BX_TEXT_PRODUCTS_GIFT_SUM; ?></strong>
                      </td>
                      <td class="dataTableContent">
                        <div class="input-row" style="display: flex; gap: 10px;">
                          <div style="flex: 1; display: flex; flex-direction: column;">
                            <?php
                              echo xtc_draw_input_field('products_gift_sum', '', 'id="gift_sum_netto_0" placeholder="Netto eingeben..."');
                            ?>
                          </div>
                          <div style="flex: 1; display: flex; flex-direction: column;">
                            <?php
                              echo xtc_draw_input_field('dummy', '', 'id="gift_sum_brutto_0" placeholder="Brutto eingeben..."');
                            ?>
                          </div>
                        </div>
                      </td>
                      <td class="dataTableContent">
                        <?php echo '<input type="submit" class="button" onclick="this.blur(); return bxValidateGiftGroups(this.form);" value="' . BUTTON_SAVE . '"/>'; ?>
                      </td>
                    </tr>
                    <tr>
                      <td class="dataTableContent" style="vertical-align: top !important;">
                        <strong><?php echo BX_TEXT_PRODUCTS_GIFT_GROUP; ?></strong>
                      </td>
                      <td class="dataTableContent">
                        <div class="main customers-groups">
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
                              echo $permission_checkboxes;
                          }
                        ?>
                        </div>
                      </td>
                      <td class="dataTableContent">&nbsp;</td>
                      <td class="dataTableContent">&nbsp;</td>
                      <td class="dataTableContent">&nbsp;</td>
                    </tr>
                  </table>
                </form>
              </div>

              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent txta-c" width="5%"><?php echo BX_TEXT_PRODUCTS_GIFT_ID; ?></td>
                  <td class="dataTableHeadingContent" width="60%"><?php echo BX_TEXT_PRODUCTS_NAME; ?></td>
                  <td class="dataTableHeadingContent" width="15%"><?php echo BX_TEXT_PRODUCTS_GIFT_SUM; ?></td>
                  <td class="dataTableHeadingContent" width="10%" colspan="2"><?php echo BX_TEXT_PRODUCTS_GIFT_ACTION; ?></td>
                </tr>
                <?php
                // eingestellte Artikel anzeigen
                $products_gift_table = "SELECT 
                                          pg.products_gift_id, 
                                          pg.products_gift_sum,
                                          pg.customers_groups,
                                          p.products_tax_class_id,
                                          pd.products_name
                                        FROM ".
                                          TABLE_PRODUCTS." p,".
                                          TABLE_PRODUCTS_DESCRIPTION." pd,".
                                          TABLE_BX_PRODUCTS_GIFT." pg
                                        WHERE 
                                          pg.products_id = p.products_id
                                          AND p.products_id = pd.products_id
                                          AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                        ORDER BY pg.products_gift_sum ASC";
			
                $products_gift_table_query = xtc_db_query($products_gift_table);
                $i = 0;
                while($products_gift = xtc_db_fetch_array($products_gift_table_query)) {
                  $products_gift['products_name'] = empty($products_gift['products_name']) ? BX_TEXT_TRANSLATION_MISSING : $products_gift['products_name'];
                ?>
                <tr class="dataTableRow">
                  <td class="dataTableContent txta-c"><?php echo $products_gift['products_gift_id'];?></td>
                  <td class="dataTableContent">
                  <?php 
                  echo $products_gift['products_name'] . xtc_draw_hidden_field('products_gift_id',$products_gift['products_gift_id']);
                  echo '<br><span class="extra_fast">'.BX_TEXT_PRODUCTS_GIFT_GROUP.':</span> ';

                  $group_list       = explode(',', $products_gift['customers_groups']);
                  $group_list_count = count($group_list);
  
                  for($a = 0; $a < $group_list_count; $a++) {
                    $group_list[$a] = xtc_get_customers_status_name($group_list[$a], $_SESSION['languages_id']);
                  }
                  echo implode(', ', $group_list);
                  ?></td>
                  <td class="dataTableContent">

                    <div class="input-row" style="display: flex; gap: 10px;">
                      <div style="flex: 1; display: flex; flex-direction: column;">
                        <label> <?php echo BX_TEXT_NET; ?>
                        <?php
                          $gift_sum_tax_rate = xtc_get_tax_rate($products_gift['products_tax_class_id']);

                          // Name bleibt "products_gift_sum", damit case 'update' das Feld findet; id nur für die Netto/Brutto-JS-Kopplung eindeutig je Zeile
                          echo xtc_draw_input_field('products_gift_sum', $products_gift['products_gift_sum'], 'id="gift_sum_netto_'.$products_gift['products_gift_id'].'" data-tax-rate="' . (float)$gift_sum_tax_rate . '" placeholder="Netto eingeben..." size="10"');
                        ?>
                        </label>
                      </div>
                      <div style="flex: 1; display: flex; flex-direction: column;">
                        <label> <?php echo BX_TEXT_GROSS; ?>
                        <?php
                          echo xtc_draw_input_field('dummy_'.$products_gift['products_gift_id'], '', 'id="gift_sum_brutto_'.$products_gift['products_gift_id'].'" data-tax-rate="' . (float)$gift_sum_tax_rate . '" placeholder="Brutto eingeben..." size="10"');
                        ?>
                        </label>
                      </div>
                    </div>

                  </td>
                  <td class="dataTableContent" style="vertical-align: bottom !important;">
                    <!-- Button ruft JS-Funktion mit der ID auf -->
                     <img src="images/icons/icon_save_50.png" onclick="saveRow(<?php echo $products_gift['products_gift_id']; ?>);" style="cursor:pointer; max-height: 24px;" alt="Speichern" />
                     
                    <?php
                      echo '<a href="'.xtc_href_link(FILENAME_BX_PRODUCTS_GIFT,'action=delete&amp;id='.$products_gift['products_gift_id']).'">'
                      .xtc_image(DIR_WS_IMAGES.'icons/icon_delete_50.png', BX_TEXT_PRODUCTS_GIFT_DELETE, '', '', 'style="max-height: 24px;"').'</a>';
                    ?>
                  </td>
                </tr>
              <?php
                  $i++;
                }
              ?>
              </table>





              </div>
            </div>

          </td>
          <td class="boxRight">
<?php

  $heading  = array();
  $contents = array();

  $heading[]  = array('text' => '<strong>'.BX_HEADING_RIGHT.'</strong>');
  $contents[] = array('text' => BX_CONTENT_RIGHT);

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
			alert('Bitte mindestens eine Kundengruppe auswählen.');
			return false;
		}
		return true;
	}
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES.'application_bottom.php'); ?>
