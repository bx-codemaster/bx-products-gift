<?php
/* -----------------------------------------------------------------------------------------
   BX Products Gift - CSRF Exclusion
   
   WICHTIG: Diese Datei fügt bx_products_gift zur CSRF-Token-Ausnahmeliste hinzu.
   
   Grund:
   - Bei Batch-Migration (100 Dateien pro Request) rotiert Modified eCommerce den CSRF-Token
   - JavaScript holt zwar vor jedem Batch einen frischen Token via get_csrf_token
   - ABER: Der Token rotiert bereits WÄHREND des vorherigen Batches
   - Resultat: Batch 3+ schlägt mit "CSRF-Token ungültig" fehl
   
   Lösung:
   - bx_products_gift wird zur Exclusion-Liste hinzugefügt
   - CSRF-Check wird für diese Datei KOMPLETT übersprungen
   - Sicherheit: Admin-Login ist bereits vorgeschaltet (application_top.php)
   
   @see csrf_token.inc.php (Line 27-54)
   @author BX Solutions
   @date 2025-11-06
   -----------------------------------------------------------------------------------------*/

// Füge bx_products_gift zur Module-Exclusion-Liste hinzu

if (!isset($module_exclusions) || !is_array($module_exclusions)) {
    $module_exclusions = array();
}

$module_exclusions[] = 'bx_products_gift';
