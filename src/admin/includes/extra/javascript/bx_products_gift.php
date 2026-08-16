<?php 
/**
 * Javascript für die Modulversion (read-only)
 */
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_BX_PRODUCTS_GIFT_STATUS') && 'True' == MODULE_BX_PRODUCTS_GIFT_STATUS && basename($_SERVER['PHP_SELF']) == 'bx_products_gift.php') {
    
    $gift_sum_tax_rate = xtc_get_tax_rate('1');
?>
<script>
// Das Modul verwendet eine IIFE, damit Hilfsvariablen und interne Funktionen
// nicht in den globalen JavaScript-Namensraum gelangen.
(function() {
    // Verbindet alle Netto-/Brutto-Felder miteinander, die beim Laden der
    // Seite bereits im DOM vorhanden sind.
    function initProductsGift() {
        /* BOF Calculate NET / GROSS */

        // Bindet ein Netto-Feld und das zugehörige Brutto-Feld. Die Zuordnung
        // erfolgt über die gleiche ID-Endung, zum Beispiel die Geschenkartikel-ID.
        function bindNetGrossPair(nettoInput, bruttoInput) {
            // Ohne beide Felder kann keine gegenseitige Berechnung eingerichtet werden. Dieser Fall wird still übersprungen.
            if (!nettoInput || !bruttoInput) {
                return;
            }

            // Der Steuersatz steht am jeweiligen Netto-Feld. Dadurch kann jede Geschenkartikelzeile eine andere Steuerklasse verwenden.
            // Aus einem Steuersatz von beispielsweise 19 wird der Faktor 1.19.
            var taxRate = parseFloat(nettoInput.getAttribute('data-tax-rate')) || 0;
            var faktor = 1 + (taxRate / 100);

            // Beim Laden wird ein vorhandener Netto-Wert einmalig in Brutto umgerechnet, damit beide Felder sofort synchron sind.
            if (nettoInput.value && !isNaN(parseFloat(nettoInput.value.replace(',', '.')))) {
                const initialNetto = parseFloat(nettoInput.value.replace(',', '.'));
                bruttoInput.value = (initialNetto * faktor).toFixed(4);
            }

            // Eingaben mit deutschem Dezimaltrennzeichen werden akzeptiert.
            // Eine gültige Netto-Eingabe aktualisiert unmittelbar den Brutto-Wert.
            nettoInput.addEventListener('input', function () {
                let wert = parseFloat(this.value.replace(',', '.'));
                if (!isNaN(wert)) {
                    bruttoInput.value = (wert * faktor).toFixed(4);
                } else {
                    // Bei einer leeren oder ungültigen Eingabe darf kein alter
                    // berechneter Wert im Brutto-Feld stehen bleiben.
                    bruttoInput.value = '';
                }
            });

            // Umgekehrte Richtung: Wird Brutto geändert, wird Netto durch den
            // Steuerfaktor zurückgerechnet.
            bruttoInput.addEventListener('input', function () {
                let wert = parseFloat(this.value.replace(',', '.'));
                if (!isNaN(wert)) {
                    nettoInput.value = (wert / faktor).toFixed(4);
                } else {
                    // Auch hier werden ungültige Eingaben aus dem Partnerfeld
                    // entfernt, damit keine widersprüchlichen Werte verbleiben.
                    nettoInput.value = '';
                }
            });
        }

        // Die ID-Präfixe unterscheiden Netto- und Brutto-Felder. Die jeweils
        // dahinter stehende ID-Endung identifiziert denselben Datensatz.
        const NETTO_PREFIX  = 'gift_sum_netto_';
        const BRUTTO_PREFIX = 'gift_sum_brutto_';

        // Alle passenden Felder der aktuellen Adminseite einsammeln.
        const nettoInputs  = document.querySelectorAll('[id^="' + NETTO_PREFIX + '"]');
        const bruttoInputs = document.querySelectorAll('[id^="' + BRUTTO_PREFIX + '"]');

        // Brutto-Felder nach ihrer ID-Endung indizieren. So kann für jedes
        // Netto-Feld das Partnerfeld direkt gefunden werden, ohne die gesamte
        // Brutto-Liste erneut durchsuchen zu müssen.
        const bruttoBySuffix = new Map();
        bruttoInputs.forEach(function (el) {
            const suffix = el.id.slice(BRUTTO_PREFIX.length);
            bruttoBySuffix.set(suffix, el);
        });

        // Jedes Netto-Feld mit dem Brutto-Feld gleicher ID-Endung verbinden.
        // Beispiel: gift_sum_netto_12 gehört zu gift_sum_brutto_12.
        nettoInputs.forEach(function (nettoInput) {
            const suffix      = nettoInput.id.slice(NETTO_PREFIX.length);
            const bruttoInput = bruttoBySuffix.get(suffix);

            if (!bruttoInput) {
                // Ein unvollständiges HTML-Paar darf die Initialisierung der übrigen Geschenkartikel nicht unterbrechen.
                console.warn('ProductsGift: Kein passendes Brutto-Feld für "' + nettoInput.id + '" gefunden.');
                return;
            }

            bindNetGrossPair(nettoInput, bruttoInput);
        });
        /* EOF Calculate NET / GROSS */
    }

    // Wird das Script im <head> geladen, wartet es auf den DOMContentLoaded-Event.
    // Bei später Einbindung ist der DOM bereits verfügbar und die Initialisierung kann sofort erfolgen.
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initProductsGift);
    } else {
        initProductsGift();
    }

    // Speichert den Netto-Wert einer einzelnen Geschenkartikelzeile per AJAX.
    // Die Funktion wird weiter unten an window gehängt, weil der Speichern-Button im PHP-HTML über onclick="saveRow(...)" auf sie zugreift.
    function saveRow(giftId) {
        // Der Feldname bleibt products_gift_sum, die ID ist pro Zeile eindeutig.
        // Dadurch wird genau der Wert der angeklickten Zeile gespeichert.
        var sumVal = $('#gift_sum_netto_' + giftId).val();

        // Der PHP-Handler erkennt action=update und erhält die Datensatz-ID
        // zusammen mit dem eingegebenen Netto-Betrag.
        $.post('<?php echo FILENAME_BX_PRODUCTS_GIFT; ?>?action=update', {
            save_id: giftId,
            products_gift_sum: sumVal
        }, function(response) {
            // Eine erfolgreiche Serverantwort wird durch kurzes grünes
            // Aufleuchten beider zusammengehöriger Eingabefelder angezeigt.
            $('#gift_sum_netto_' + giftId).css('background-color', '#36e25e');
            $('#gift_sum_brutto_' + giftId).css('background-color', '#36e25e');
            setTimeout(function() {
                $('#gift_sum_netto_' + giftId).css('background-color', '');
                $('#gift_sum_brutto_' + giftId).css('background-color', '');
            }, 1000);
        }).fail(function() {
            // Bei Netzwerkfehlern oder einer nicht erfolgreichen AJAX-Antwort
            // wird die betroffene Zeile kurz rot markiert.
            $('#gift_sum_netto_' + giftId).css('background-color', '#f08a8a');
            $('#gift_sum_brutto_' + giftId).css('background-color', '#f08a8a');
            setTimeout(function() {
                $('#gift_sum_netto_' + giftId).css('background-color', '');
                $('#gift_sum_brutto_' + giftId).css('background-color', '');
            }, 1000);
        });
    }

    // Inline-onclick liegt außerhalb der IIFE und kann interne Funktionen nicht direkt erreichen.
    // Diese explizite Freigabe stellt den Button-Aufruf her.
    window.saveRow = saveRow;
})();
</script>
<?php
  }
