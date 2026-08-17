<?php 
/**
 * Javascript für die Modulversion (read-only)
 */
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_BX_PRODUCTS_GIFT_STATUS') && 'True' == MODULE_BX_PRODUCTS_GIFT_STATUS && basename($_SERVER['PHP_SELF']) == 'bx_products_gift.php') {
    
    /** 
     * CSRF-Token für AJAX-Requests vorbereiten. Der Wert wird als JSON-Fragment aufgebaut, 
     * damit er sicher in den $.post-Aufruf eingebettet werden kann, unabhängig davon, welche Zeichen Session-Name/-Token enthalten.
     */
    $addPayload = '';
    if (isset($_SESSION['CSRFName']) && isset($_SESSION['CSRFToken'])) {
      $addPayload = json_encode($_SESSION['CSRFName']) . ': ' . json_encode($_SESSION['CSRFToken']) . ',' . PHP_EOL;
    }
?>
<script>
/** 
 * Das Modul verwendet eine IIFE, damit Hilfsvariablen und interne Funktionen nicht in den globalen JavaScript-Namensraum gelangen.
 * Die Initialisierung erfolgt beim DOMContentLoaded-Event, damit die DOM-Elemente bereits verfügbar sind.
 */
(function() {
    /** 
     * Bindet ein Netto-Feld und das zugehörige Brutto-Feld. 
     * Die Zuordnung erfolgt über die gleiche ID-Endung, zum Beispiel die Geschenkartikel-ID.
     */
    function bindNetGrossPair(nettoInput, bruttoInput) {
        // Ohne beide Felder kann keine gegenseitige Berechnung eingerichtet werden. Dieser Fall wird still übersprungen.
        if (!nettoInput || !bruttoInput) {
            return;
        }

        // Der Steuersatz steht am jeweiligen Netto-Feld. Dadurch kann jede Geschenkartikelzeile eine andere Steuerklasse verwenden.
        var taxRate = parseFloat(nettoInput.getAttribute('data-tax-rate')) || 0;
        var faktor = 1 + (taxRate / 100);

        // Beim Laden wird ein vorhandener Netto-Wert einmalig in Brutto umgerechnet, damit beide Felder sofort synchron sind.
        if (nettoInput.value && !isNaN(parseFloat(nettoInput.value.replace(',', '.')))) {
            const initialNetto = parseFloat(nettoInput.value.replace(',', '.'));
            bruttoInput.value = (initialNetto * faktor).toFixed(3);
        }

        /** 
         * Eingaben mit deutschem Dezimaltrennzeichen werden akzeptiert. 
         * Eine gültige Netto-Eingabe aktualisiert unmittelbar den Brutto-Wert.
         */
        nettoInput.addEventListener('input', function () {
            let wert = parseFloat(this.value.replace(',', '.'));
            if (!isNaN(wert)) {
                bruttoInput.value = (wert * faktor).toFixed(3);
            } else {
                bruttoInput.value = '';
            }
        });

        /**
         * Umgekehrte Richtung: Wird Brutto geändert, wird Netto durch den Steuerfaktor zurückgerechnet.
         */
        bruttoInput.addEventListener('input', function () {
            let wert = parseFloat(this.value.replace(',', '.'));
            if (!isNaN(wert)) {
                nettoInput.value = (wert / faktor).toFixed(3);
            } else {
                nettoInput.value = '';
            }
        });
    }

    /** 
     * Initialisiert alle Netto-/Brutto-Paare innerhalb eines Containers. 
     * Der Container kann das gesamte Dokument oder eine neu eingefügte Tabellenzeile sein.
     */
    function initGiftNettoBruttoCalculation(container) {
        var root = container || document;
        var nettoInputs    = root.querySelectorAll('[id^="gift_sum_netto_"]');
        var bruttoInputs   = root.querySelectorAll('[id^="gift_sum_brutto_"]');
        var bruttoBySuffix = new Map();

        bruttoInputs.forEach(function (el) {
            var suffix = el.id.slice('gift_sum_brutto_'.length);
            bruttoBySuffix.set(suffix, el);
        });

        nettoInputs.forEach(function (nettoInput) {
            var suffix = nettoInput.id.slice('gift_sum_netto_'.length);
            var bruttoInput = bruttoBySuffix.get(suffix);

            if (bruttoInput) {
                bindNetGrossPair(nettoInput, bruttoInput);
            }
        });
    }

    /**
     * Speichert den Netto-Wert einer einzelnen Geschenkartikelzeile per AJAX.
     * Die Funktion wird weiter unten an window gehängt, weil der Speichern-Button im PHP-HTML über onclick="saveRow(...)" auf sie zugreift.
     */
    function saveRow(giftId) {
        // Der Feldname bleibt products_gift_sum, die ID ist pro Zeile eindeutig.
        // Dadurch wird genau der Wert der angeklickten Zeile gespeichert.
        var sumVal = $('#gift_sum_netto_' + giftId).val().replace(',', '.');

        // Der PHP-Handler erkennt action=update und erhält die Datensatz-ID
        // zusammen mit dem eingegebenen Netto-Betrag.
        $.post(<?php echo json_encode(FILENAME_BX_PRODUCTS_GIFT . '?action=update'); ?>, {
            save_id: giftId,
            products_gift_sum: sumVal,
            <?php echo $addPayload; ?>
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

    /**
     * Lädt die gespeicherten Geschenkartikel per AJAX und fügt die Zeilen zeitversetzt ein, 
     * damit die Tabelle nicht gleichzeitig vollständig aufgebaut werden muss und die Einblendanimation sichtbar bleibt.
     */
    function loadGiftTableStaggered(delayMs) {
        var $tbody = $('#bx-gift-table-body');

        // Während des Ladens wird ein neutraler Status in der Tabelle angezeigt.
        $tbody.html('<tr><td colspan="5" class="txta-c" style="padding: 15px;"><?php echo BX_TEXT_LOADING_PRODUCTS; ?></td></tr>');

        /**
         * Die Tabelle wird als JSON geladen. Die PHP-Antwort enthält die bereits aufbereiteten Werte 
         * für Name, Gruppen, Betrag und Steuersatz.
         */
        $.getJSON('bx_products_gift.php?action=get_gift_table', function(response) {
            // Bei einer fehlgeschlagenen Antwort bleibt der Ladestatus bestehen,
            // damit kein scheinbar leerer Tabelleninhalt angezeigt wird.
            if (!response.success) return;
            
            $tbody.empty();
            var items = response.items;

            // Ein erfolgreicher Aufruf kann trotzdem keine gespeicherten Geschenkartikel liefern.
            if (items.length === 0) {
                $tbody.html('<tr><td colspan="5" class="txta-c"><?php echo BX_TEXT_NO_PRODUCTS_AVAILABLE; ?></td></tr>');
                return;
            }

            var index = 0;

            // Jede Zeile wird mit dem gewünschten Abstand einzeln eingefügt.
            // Dadurch erscheinen die Datensätze kontrolliert nacheinander.
            var interval = setInterval(function() {
                if (index >= items.length) {
                    clearInterval(interval);
                    return;
                }

                var item = items[index];

                // Die Zeile wird als HTML aufgebaut. IDs und data-tax-rate
                // stellen sicher, dass die Netto-/Brutto-Felder je Datensatz
                // eindeutig bleiben und korrekt berechnet werden können.
                var rowHtml = `
                <tr class="dataTableRow" style="display: none;">
                    <td class="dataTableContent txta-c">${item.id}</td>
                    <td class="dataTableContent">
                    ${item.name} <input type="hidden" name="products_gift_id" value="${item.id}">
                    <br><span class="extra_fast"><?php echo BX_TEXT_PRODUCTS_GIFT_GROUP; ?>:</span> ${item.groups}
                    </td>
                    <td class="dataTableContent">
                    <div class="gift-select-row">
                        <label><?php echo BX_TEXT_NET; ?>
                        <input type="text" name="products_gift_sum" value="${item.sum}" id="gift_sum_netto_${item.id}" data-tax-rate="${item.tax_rate}" placeholder="<?php echo BX_TEXT_ENTER_NET; ?>" size="10">
                        </label>
                        <label><?php echo BX_TEXT_GROSS; ?>
                        <input type="text" name="dummy_${item.id}" value="" id="gift_sum_brutto_${item.id}" data-tax-rate="${item.tax_rate}" placeholder="<?php echo BX_TEXT_ENTER_GROSS; ?>" size="10">
                        </label>
                    </div>
                    </td>
                    <td class="dataTableContent">${item.created_at}</td>
                    <td class="dataTableContent editButtons txta-c">
                    <a href="javascript:void(0)" onclick="saveRow(${item.id});">  
                        <img src="images/icons/icon_save_50.png" title="<?php echo BX_TEXT_PRODUCTS_GIFT_SUM_UPDATE; ?>" style="max-height: 24px;" alt="<?php echo BX_TEXT_PRODUCTS_GIFT_SAVE; ?>" />
                    </a>
                    <a href="javascript:void(0)" onclick="return confirmLink('<?php echo BX_TEXT_PRODUCTS_GIFT_CONFIRM_DELETE; ?>', '<?php echo BX_TEXT_PRODUCTS_GIFT_DELETE; ?>', 'bx_products_gift.php?action=delete&amp;id=${item.id}');">
                        <?php echo xtc_image(DIR_WS_IMAGES.'icons/icon_delete_50.png', BX_TEXT_PRODUCTS_GIFT_DELETE, '', '', 'style="max-height: 24px;"'); ?>
                    </a>
                    </td>
                </tr>
                `;

                // Die neue Zeile zunächst unsichtbar einfügen und anschließend weich einblenden.
                var $row = $(rowHtml);
                $tbody.append($row);
                $row.hide().fadeIn("slow"); // Sanftes Einblenden

                // Falls dynamisch eingefügte Netto-/Brutto-Felder eine erneute
                // Initialisierung benötigen, wird die dafür vorgesehene
                // Funktion auf die aktuelle Zeile begrenzt aufgerufen.
                if (typeof initGiftNettoBruttoCalculation === 'function') {
                    initGiftNettoBruttoCalculation($row[0]);
                }

                index++;
            }, delayMs);
        });
    }

    /** 
     * Verbindet alle Netto-/Brutto-Felder miteinander, die beim Laden der Seite bereits im DOM vorhanden sind. 
     */
    function initProductsGift() {
        loadGiftTableStaggered(100); // 100 ms Verzögerung zwischen den Zeilen, um die Ladeanimation zu sehen

        initGiftNettoBruttoCalculation(document);

        /** 
         * Die Kategorieauswahl steuert die abhängige Produktauswahl. 
         * Beide nativen Select-Elemente bleiben als Referenz erhalten, 
         * auch wenn SumoSelect zusätzlich eine sichtbare eigene Oberfläche erzeugt.
         */
        var categorySelect = document.querySelector('select[name="catID"]');
        var productSelect  = document.querySelector('select[name="products_gift"]');

        if (categorySelect && productSelect) {
            // Verhindert, dass der SumoSelect-Klick und das dadurch ausgelöste native change-Event denselben AJAX-Aufruf doppelt starten.
            var lastCategoryId = 0;
            var lastCategoryRequestAt = 0;

            // Aktualisiert das Produktfeld unabhängig davon, ob SumoSelect initialisiert wurde oder nur das native Select verfügbar ist.
            function setProductOptions(options) {
                if (productSelect.sumo) {
                    /**
                     * SumoSelect verwaltet neben dem Select eine sichtbare Optionsliste. 
                     * Deshalb müssen vorhandene Einträge über die SumoSelect-API entfernt und neu angelegt werden.
                     */
                    while (productSelect.options.length > 0) {
                        productSelect.sumo.remove(0);
                    }

                    // add() synchronisiert das native option-Element und den sichtbaren SumoSelect-Eintrag einschließlich Klicklogik.
                    options.forEach(function (option) {
                        productSelect.sumo.add(option.id, option.text);
                    });

                    /**
                     * Der erste Eintrag ist der Platzhalter. 
                     * Er wird aktiv gesetzt, damit die SumoSelect-Caption sofort den Text "Bitte Produkt wählen" 
                     * beziehungsweise den aktuellen Lade- oder Fehlerstatus anzeigt.
                     */
                    productSelect.sumo.selectItem(0);
                    return;
                }

                // Fallback für den Fall, dass SumoSelect nicht verfügbar ist. Dann wird ausschließlich die native Optionsliste aufgebaut.
                productSelect.innerHTML = '';
                options.forEach(function (option) {
                    productSelect.appendChild(new Option(option.text, option.id));
                });

                // Auch im nativen Select bleibt der Platzhalter ausgewählt.
                productSelect.selectedIndex = 0;
            }

            // Lädt die Produkte der gewählten Kategorie per AJAX.
            function loadProducts(categoryId) {
                /**
                 * Die Kategorie-ID stammt aus einem Select bzw. aus data-val. 
                 * Die Konvertierung verhindert, dass unerwartete Werte an den  Server weitergereicht werden.
                 */
                categoryId = parseInt(categoryId, 10) || 0;

                /**
                 * SumoSelect löst beim Klick sowohl eigene als auch native Events aus. 
                 * Ein identischer Aufruf innerhalb von 500 ms wird deshalb als Duplikat verworfen.
                 */
                var now = Date.now();
                if (categoryId === lastCategoryId && now - lastCategoryRequestAt < 500) {
                    return;
                }
                lastCategoryId = categoryId;
                lastCategoryRequestAt = now;

                // Sofortige Rückmeldung während der Serveranfrage. Bei einer ungültigen Kategorie wird nur der Auswahlhinweis angezeigt.
                setProductOptions([{
                    id: '',
                    text: categoryId > 0
                        ? <?php echo json_encode(BX_TEXT_LOADING_PRODUCTS); ?>
                        : <?php echo json_encode(BX_TEXT_SELECT_CATEGORY); ?>
                }]);

                if (categoryId <= 0) {
                    return;
                }

                /**
                 * Der Server filtert die Produkte nach Kategorie und entfernt bereits verwendete Geschenkartikel. 
                 * Die Antwort enthält ausschließlich JSON für die nachfolgende Select-Befüllung.
                 */
                $.ajax({
                    url: <?php echo json_encode(FILENAME_BX_PRODUCTS_GIFT); ?>,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'load_products',
                        catID: categoryId
                    },
                    success: function (response) {
                        // Eine gültige Antwort ohne Produkte erhält einen eigenen Hinweis statt einer leeren Select-Caption.
                        if (!response.success || !response.products.length) {
                            setProductOptions([{
                                id: '',
                                text: <?php echo json_encode(BX_TEXT_NO_PRODUCTS_AVAILABLE); ?>
                            }]);
                            return;
                        }

                        // Der Platzhalter bleibt der erste Eintrag. Danach folgen die vom Server gelieferten Produkte.
                        setProductOptions([{
                            id: '',
                            text: <?php echo json_encode(BX_TEXT_SELECT_PRODUCT); ?>
                        }].concat(response.products));
                    },
                    error: function () {
                        // Netzwerk- oder Serverfehler werden direkt im Produktfeld sichtbar gemacht.
                        setProductOptions([{
                            id: '',
                            text: <?php echo json_encode(BX_TEXT_PRODUCTS_LOAD_FAILED); ?>
                        }]);
                    }
                });
            }

            // Fallback für native Select-Änderungen und programmgesteuerte Änderungen am Kategorie-Select.
            categorySelect.addEventListener('change', function () {
                loadProducts(this.value);
            });

            /**
             * SumoSelect triggert jQuery-Events. 
             * Der Handler synchronisiert den selected-Status des nativen Selects mit der sichtbaren Wahl.
             */
            $(productSelect).on('change', function () {
                var selectedValue = this.value;

                Array.prototype.forEach.call(this.options, function (option) {
                    var isSelected = option.value === selectedValue;
                    option.selected = isSelected;

                    // Zusätzlich zur DOM-Eigenschaft wird das HTML-Attribut gepflegt, damit beide Zustände eindeutig übereinstimmen.
                    if (isSelected) {
                        option.setAttribute('selected', 'selected');
                    } else {
                        option.removeAttribute('selected');
                    }
                });
            });

            /**
             * Bei aktiviertem SumoSelect klickt der Benutzer auf ein sichtbares li[data-val]. 
             * Die Delegation hängt den Handler an document, damit auch später dynamisch erzeugte Kategorieoptionen erfasst werden.
             */
            $(document).on('click', '.catid .options li', function () {
                loadProducts($(this).attr('data-val'));
            });
        }

    }

    /**
     * Wird das Script im <head> geladen, wartet es auf den DOMContentLoaded-Event. 
     * Bei später Einbindung ist der DOM bereits verfügbar und die Initialisierung kann sofort erfolgen.
     */
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initProductsGift);
    } else {
        initProductsGift();
    }

    /**
     * Inline-onclick liegt außerhalb der IIFE und kann interne Funktionen nicht direkt erreichen. 
     * Diese explizite Freigabe stellt den Button-Aufruf her.
     */
    window.saveRow = saveRow;
})();
</script>
<?php
  }
