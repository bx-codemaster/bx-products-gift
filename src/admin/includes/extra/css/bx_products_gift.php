<?php 
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_BX_PRODUCTS_GIFT_STATUS') && 'True' == MODULE_BX_PRODUCTS_GIFT_STATUS && basename($_SERVER['PHP_SELF']) == 'bx_products_gift.php') {
?>
<style>
.header_gift {
  background-color: #dddddd; 
  border-bottom: 2px solid #ffffff; 
  font-family:Verdana, Arial, sans-serif; 
  font-size:10px; 
  color:#000000;
}

.content_gift {
  border-bottom: 1px solid #ffffff; 
  font-family:Verdana, Arial, sans-serif; 
  font-size:10px; 
  color:#000000;
}

.gift-select-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1fr);
  gap: 10px;
  width: 100%;
}

.gift-select-subrow {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 10px;
  width: 100%;
}

.gift-select-column {
  display: grid;
  grid-template-rows: auto minmax(0, 1fr);
  align-items: start;
  min-width: 0;
}

p.column-label {
  font-weight: bold;
  margin: 0 0 5px 0;
}

.gift-select-column > .SumoSelect {
  display: block;
  width: 100%;
  min-width: 0 !important;
}

.gift-select-column > .SumoSelect > .SlectBox {
  display: none;
}

.gift-select-column > .SumoSelect > .CaptionCont {
  display: block;
  width: 100%;
  min-height: 20px;
  margin: 0;
  box-sizing: border-box;
}

.gift-select-column > .SumoSelect > .CaptionCont > span {
  display: block;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}

.gift-select-column > .SumoSelect > .optWrapper {
  width: 100%;
}

.gift-customer-groups {
  display: block;
  width: auto;
  max-width: 100%;
  padding: 5px;
  border: 1px solid #DAA520;
  background: #fff0cf;

}

.extra_fast {
  font-weight: bold;
  color: #AF417E;
}

.dataTableContent.editButtons {
  vertical-align: bottom !important;
}

.dataTableContent.editButtons a:hover {
  text-decoration: none;
}

.gift-customer-groups .ChkBox {
    /* das native Checkbox-Icon des Browsers ausblenden */
    opacity: 0;
    position: absolute;
}

.gift-customer-groups .ChkBox + em {
    display: inline-block;
    width: 16px;
    height: 16px;
    background: url('checkbox-unchecked.png');
    /* oder: border + border-radius, um eine eigene Box zu zeichnen */
}

.gift-customer-groups .ChkBox:checked + em {
    background: url('checkbox-checked.png');
    /* oder eine grüne Hintergrundfarbe, ein ✓-Icon per ::after usw. */
}

.gift-customer-groups input[type="checkbox"].ChkBox:not(old), 
.gift-customer-groups input[type="radio"].ChkBox:not(old) {
  margin: 5px 0px 0px 5px;
  padding: 0;
  opacity: 0;
  transform: none;
}

.gift-customer-groups input[type="checkbox"].ChkBox:not(old):not([disabled]) + em, 
.gift-customer-groups input[type="radio"].ChkBox:not(old):not([disabled]) + em {
  margin-left: 0 !important;
  padding-left: 20px !important;
  width: 16px !important;
  height: 20px !important;
}
.gift-customer-groups label {
  font-weight: bold;
  font-size: 10px;
  color: #AF417E;
}
.boxRight .infoBoxContent {
  font-size: 12px;
}
</style>

<?php } ?>