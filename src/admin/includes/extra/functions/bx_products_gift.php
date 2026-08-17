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
 * Konfigurationseingabefeld für die Modulversion (read-only)
 */
if (!function_exists('bx_configuration_field_version')) {
  function bx_configuration_field_version(string $value, string $constant): string {
    return xtc_draw_input_field( 'configuration['.$constant.']', $value, 'readonly="true" style="opacity: 0.4;"');
  }
}
