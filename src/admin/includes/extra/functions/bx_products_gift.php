<?php
// Prüfung ob Artikel als Gratisartikel zugelassen
// return false oder products_id
function pg_check_gift( int $products_gift_id ): bool {
  global $xtPrice;
  
  if( $_SESSION['cart']->cart_has_products_gift() ) {     // schon gratisartikel in wk?
    return false;                                         // nein, ende
  }
                                                          // Artikel in Gratistabelle?  
  $sql = "SELECT 
            products_id,
            products_gift_sum,
            customers_groups
          FROM ".TABLE_BX_PRODUCTS_GIFT."
          WHERE products_gift_id='".$products_gift_id."'";

  $res  = xtc_db_query($sql);
  $data = xtc_db_fetch_array($res);

  if( $data === false ) {                             
    return false;                                         // nein, ende
  }
                                                          // passende kundengruppe?
  $group_list = explode(',', $data['customers_groups']);
  if( !in_array( $_SESSION['customers_status']['customers_status_id'], $group_list) ) {
    return false;                                         // nein, ende
  }
  
  $fdc              = 1.0/$xtPrice->currencies[$_SESSION['currency']]['value'];  // Währungsfaktor (falls Währung!=Defaultwährung)
  $cart_sum         = $_SESSION['cart']->show_total();                           // wk summe
  $cart_sum_defcurr = $cart_sum*$fdc;                                            // WK Summe in Defaultwährung
  
  if( $cart_sum_defcurr < $data['products_gift_sum'] ) {            // ausreichend bestellwert?
    return false;                                                   // nein, ende
  }
  //return $data['products_id'];                                      // ok
  return true;
}


// Prüfung ob Gratisartikel in WK noch berechtigt
function pg_check_gift_in_cart(): void {
  global $xtPrice;

  $cart_product_arr = $_SESSION['cart']->get_products();
  $remove_id_arr = array();                                         // Liste zu löschender Gratisartikel
  
  $fdc              = 1.0/$xtPrice->currencies[$_SESSION['currency']]['value'];  // Währungsfaktor (falls Währung != Defaultwährung)
  $cart_sum         = $_SESSION['cart']->show_total();                           // WK Summe
  $cart_sum_defcurr = $cart_sum*$fdc;                                            // WK Summe in Defaultwährung

  foreach( $cart_product_arr as $cart_product ) {
    if( $cart_product['products_gift'] !== true ) {           // kein gratisartikel?
      continue;                                             // Überspringen
    }

                                                            // gratisdaten lesen    
    $products_gift_id = $cart_product['products_gift_id'];
    
    if( !isset($pg_data[$products_gift_id] ) ) {            // man muss nicht bei jedem Durchlauf SQL Abfrage machen
      $sql = "SELECT 
                products_id,
                products_gift_sum,
                customers_groups
              FROM ".TABLE_BX_PRODUCTS_GIFT."
              WHERE products_gift_id='".$products_gift_id."'";

      $res = xtc_db_query($sql);
      $pg_data[$products_gift_id] = xtc_db_fetch_array($res);
    } 
    $data = $pg_data[$products_gift_id];
    
    if( $data === false ) {                                 // Gratisartikel nicht gefunden
      $_SESSION['cart']->remove($cart_product['id']);                 // löschen aus WK
      continue;                                                       // und nächster Artikel
    }
        
                                                            // passende Kundengruppe? (sollte hier nie vorkommen)
    $group_list = explode(',', $data['customers_groups']);
    if( !in_array( $_SESSION['customers_status']['customers_status_id'], $group_list) ) {
      $_SESSION['cart']->remove($cart_product['id']);                 // löschen aus WK
      continue;                                                       // und nächster Artikel
    }
    
    if( $cart_sum_defcurr < $data['products_gift_sum'] ) {            // ausreichend Bestellwert?
      $_SESSION['cart']->remove($cart_product['id']);                 // löschen aus WK
      continue;                                                       // und nächster Artikel
    }
  } // foreach
}

/**
 * Projektspezifische Erweiterung von xtc_draw_pull_down_menu()
 * aus html_output.php (modified eCommerce Shopsoftware).
 *
 * Basis: html_output.php, Funktion xtc_draw_pull_down_menu(), Zeile 271–321
 * Änderung: Unterstützung für optionale data-*-Attribute pro <option>
 *           über einen zusätzlichen Array-Key 'data' => array(...)
 *
 * Bewusst als eigene, umbenannte Funktion gehalten, um die Core-Datei
 * html_output.php unangetastet zu lassen (Updatesicherheit).
 *
 * WICHTIG: Bei Updates der Shopsoftware sollte geprüft werden, ob sich
 * die Original-Funktion xtc_draw_pull_down_menu() geändert hat, um
 * Sicherheits-/Bugfixes ggf. hier manuell nachzuziehen.
 */

if (!function_exists('bx_draw_pull_down_menu')) {
  // Output a form pull down menu (mit optionalen data-*-Attributen pro Option)
  function bx_draw_pull_down_menu(string $name, array $values, $default = '', $params = '', $required = false, $addwrap = true) {
    $field = '<select name="' . $name . '"';

    if (!is_array($values) && $values == 'checkbox') {
        $values = array(
            array('id' => 0, 'text' => CFG_TXT_NO),
            array('id' => 1, 'text' => CFG_TXT_YES)
        );
    }

    if ($addwrap && NEW_SELECT_CHECKBOX == 'true' && strpos($params, 'noStyling') === false) {
        $params = preg_replace("'\s+=\s+'", '=', $params);
        $params = (strpos($params, 'class="') !== false ? str_replace('class="', 'class="SlectBox ', $params) : $params . ' class="SlectBox"');
        $params = (strpos($params, 'style="') !== false ? str_replace('style="', 'style="visibility: hidden; ', $params) : $params . ' style="visibility: hidden;"');
    }

    if ($params) $field .= ' ' . $params;
    $field .= '>' . PHP_EOL;

    $li = '';
    $selText = '';

    if (is_array($values)) {
      foreach ($values as $key => $val) {
        $field .= '<option value="' . $val['id'] . '"';
        $li .= '<li data-val="' . $val['id'] . '"';

        // NEU: optionale data-*-Attribute, z.B. 'data' => array('tax_class_id' => 5)
        if (!empty($val['data']) && is_array($val['data'])) {
          foreach ($val['data'] as $data_key => $data_value) {
            $attr = ' data-' . htmlspecialchars($data_key, ENT_QUOTES) . '="' . htmlspecialchars($data_value, ENT_QUOTES) . '"';
            $field .= $attr;
            $li .= $attr;
          }
        }

        if ((strlen($val['id']) > 0
                && isset($GLOBALS[$name])
                && !is_object($GLOBALS[$name])
                && !is_array($GLOBALS[$name])
                && (string)$GLOBALS[$name] == (string)$val['id']
            ) || ((string)$default == (string)$val['id'])
        ) {
          $field .= ' selected="selected"';
          $li .= ' class="selected"';
          $selText = $val['text'];
        }

        $field .= '>' . $val['text'] . '</option>' . PHP_EOL;
        $li .= '>' . PHP_EOL . '<label>' . $val['text'] . '</label>' . PHP_EOL . '</li>' . PHP_EOL;
      }
    }

    $field .= '</select>' . PHP_EOL;

    if ($addwrap && NEW_SELECT_CHECKBOX == 'true' && strpos($params, 'noStyling') === false) {
      $wrapName = str_replace(array('[', ']'), array('_', ''), $name); // fix for name is array: example[...]
      $add = '<p class="CaptionCont SlectBox">' . PHP_EOL;
      $add .= '<span>' . $selText . '</span>' . PHP_EOL;
      $add .= '<label><i></i></label></p>' . PHP_EOL;
      $add .= '<div class="optWrapper">' . PHP_EOL . '<ul class="options">' . PHP_EOL . $li . PHP_EOL . '</ul>' . PHP_EOL . '</div>' . PHP_EOL;
      $field = '<div class="SumoSelect ' . strtolower($wrapName) . '" tabindex="0">' . $field . $add . '</div>';
    }

    if ($required) {
      $field .= TEXT_FIELD_REQUIRED;
    }

    return $field;
  }
}

/**
 * Konfigurationseingabefeld für die Modulversion (read-only)
 */
if (!function_exists('bx_configuration_field_version')) {
  function bx_configuration_field_version(string $value, string $constant): string {
    return xtc_draw_input_field( 'configuration['.$constant.']', $value, 'readonly="true" style="opacity: 0.4;"');
  }
}
