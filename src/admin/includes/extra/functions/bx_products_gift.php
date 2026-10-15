<?php
// Prüfung ob Artikel als Gratisartikel zugelassen
// return true, wenn der Gratisartikel dem Warenkorb hinzugefügt werden darf, sonst false
function pg_check_gift( int $products_gift_id ): bool {
  global $xtPrice;
  
  if( $_SESSION['cart']->cart_has_products_gift() ) {     // schon gratisartikel in wk?
    return false;                                         // nein, ende
  }
                                                          // Artikel in Gratistabelle?  
  $sql = "SELECT 
            products_id,
            products_gift_sum,
            customers_groups,
            trigger_category_id,
            category_match_mode
          FROM ".TABLE_BX_PRODUCTS_GIFT."
          WHERE products_gift_id='".$products_gift_id."'";

  $res  = xtc_db_query($sql);
  $data = xtc_db_fetch_array($res);

  if( $data === false ) {                             
    return false;                                         // nein, ende
  }
                                                          // passende kundengruppe?
  if( !pg_customer_group_allowed( $data['customers_groups'], $_SESSION['customers_status']['customers_status_id'] ) ) {
    return false;                                         // nein, ende
  }

  if( !pg_gift_category_allowed( (int)$data['trigger_category_id'], $data['category_match_mode'] ) ) {
    return false;
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
                customers_groups,
                trigger_category_id,
                category_match_mode
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
    if( !pg_customer_group_allowed( $data['customers_groups'], $_SESSION['customers_status']['customers_status_id'] ) ) {
      $_SESSION['cart']->remove($cart_product['id']);                 // löschen aus WK
      continue;                                                       // und nächster Artikel
    }

    if( !pg_gift_category_allowed( (int)$data['trigger_category_id'], $data['category_match_mode'] ) ) {
      $_SESSION['cart']->remove($cart_product['id']);
      continue;
    }
    
    if( $cart_sum_defcurr < $data['products_gift_sum'] ) {            // ausreichend Bestellwert?
      $_SESSION['cart']->remove($cart_product['id']);                 // löschen aus WK
      continue;                                                       // und nächster Artikel
    }
  } // foreach
}

/**
 * Prüft die Kategoriebedingung einer Produktzugabe gegen reguläre Warenkorbpositionen.
 * Kategorie 0 steht für eine globale Zugabe. Bei "all" müssen alle regulären
 * Produkte direkt der Kategorie zugeordnet sein; Geschenkartikel zählen nicht mit.
 */
if (!function_exists('pg_gift_category_allowed')) {
  function pg_gift_category_allowed( int $category_id, string $match_mode ): bool {
    if ($category_id <= 0) {
      return true;
    }

    $cart_products = $_SESSION['cart']->get_products();
    $product_ids = array();
    if (is_array($cart_products)) {
      foreach ($cart_products as $cart_product) {
        if (($cart_product['products_gift'] ?? false) === true) {
          continue;
        }

        $cart_product_id = $cart_product['products_id'] ?? $cart_product['id'] ?? 0;
        $product_id = function_exists('xtc_get_prid') ? (int)xtc_get_prid($cart_product_id) : (int)$cart_product_id;
        if ($product_id > 0) {
          $product_ids[$product_id] = $product_id;
        }
      }
    }

    if (count($product_ids) === 0) {
      return false;
    }

    $product_id_list = implode(',', array_map('intval', $product_ids));
    $category_query = xtc_db_query(
      "SELECT DISTINCT products_id
         FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
        WHERE categories_id = " . (int)$category_id . "
          AND products_id IN (" . $product_id_list . ")"
    );
    $matching_products = array();
    while ($category_product = xtc_db_fetch_array($category_query)) {
      $matching_products[(int)$category_product['products_id']] = true;
    }

    if ($match_mode === 'all') {
      return count($matching_products) === count($product_ids);
    }

    return count($matching_products) > 0;
  }
}

/**
 * Prüft, ob eine Kundengruppe für eine Produktzugabe berechtigt ist.
 *
 * Die im Admin gespeicherte Checkbox "Alle" liefert den Sonderwert 'all'
 * statt einer konkreten customers_status_id (siehe categories.php und die
 * Anzeigelogik in admin/bx_products_gift.php, die 'all' bereits als TXT_ALL
 * anzeigt). Ohne diese Sonderbehandlung greift in_array() nie, weil eine
 * numerische Kundengruppen-ID niemals dem String 'all' entspricht - jede
 * Zugabe mit "Alle" ausgewählt würde dadurch für keinen einzigen Kunden
 * funktionieren.
 *
 * @param string $customers_groups  Kommagetrennte Liste aus customers_status_id
 *                                   und/oder dem Sonderwert 'all'.
 * @param int    $customers_status_id  Kundengruppen-ID des aktuellen Besuchers.
 *
 * @return bool
 */
if (!function_exists('pg_customer_group_allowed')) {
  function pg_customer_group_allowed( string $customers_groups, int $customers_status_id ): bool {
    $group_list = array_map('trim', explode(',', $customers_groups));

    if( in_array('all', $group_list, true) ) {
      return true;
    }

    return in_array( (string)$customers_status_id, $group_list, true );
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
