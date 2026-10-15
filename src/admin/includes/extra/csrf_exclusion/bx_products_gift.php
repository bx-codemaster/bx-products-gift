<?php
/* -----------------------------------------------------------------------------------------
   BX Products Gift - CSRF Exclusion (deaktiviert)

   Diese Datei fuegte bx_products_gift frueher pauschal zur CSRF-Token-
   Ausnahmeliste hinzu und uebergingen damit den CSRF-Schutz fuer das gesamte
   Modul, einschliesslich der loeschenden Aktion (action=delete).

   Der urspruengliche Grund (Token-Rotation waehrend Batch-Verarbeitung)
   trifft auf dieses Modul nicht zu: Jede mutierende Aktion (saveRow,
   deleteRow, das "Neu anlegen"-Formular) ist ein einzelner, isolierter
   Request. Jede dieser Aktionen holt sich daher unmittelbar vor dem
   eigentlichen Request selbst einen frischen Token ueber
   action=get_csrf_token (siehe extra/javascript/bx_products_gift.php
   und den onclick-Handler bxValidateGiftGroups()). Eine pauschale
   CSRF-Ausnahme ist damit nicht mehr noetig und wurde entfernt.

   Diese Datei bleibt als No-Op bestehen, weil das Installer-Manifest
   (modulehash.json) an dieser Stelle eine Datei erwartet.

   @see admin/bx_products_gift.php (case 'get_csrf_token')
   @see admin/includes/extra/javascript/bx_products_gift.php (withFreshToken)
   @author BX Solutions
   @date 2026-08-12
   -----------------------------------------------------------------------------------------*/

// Bewusst leer: bx_products_gift wird NICHT mehr zu $module_exclusions hinzugefuegt.
