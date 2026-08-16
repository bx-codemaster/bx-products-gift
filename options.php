<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_adminses.php';

$MODULE_ID = 'bx.products.gift';

Loc::loadMessages(__FILE__);

$aTabs = [
    [
        'DIV'   => 'edit1',
        'TAB'   => Loc::getMessage('BX_PRODUCTS_GIFT_OPTIONS_TAB'),
        'ICON'  => '',
        'TITLE' => Loc::getMessage('BX_PRODUCTS_GIFT_OPTIONS_TAB_TITLE'),
    ],
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    Option::set($MODULE_ID, 'gift_product_id', (int)$_POST['gift_product_id']);
    Option::set($MODULE_ID, 'gift_quantity',   max(1, (int)$_POST['gift_quantity']));

    LocalRedirect($APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . urlencode($MODULE_ID) . '&saved=Y');
}

$giftProductId = (int)Option::get($MODULE_ID, 'gift_product_id', 0);
$giftQuantity  = (int)Option::get($MODULE_ID, 'gift_quantity',   1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if ($_GET['saved'] === 'Y') {
    CAdminMessage::ShowMessage(['TYPE' => 'OK', 'MESSAGE' => Loc::getMessage('BX_PRODUCTS_GIFT_OPTIONS_SAVED')]);
}

?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?lang=<?= LANGUAGE_ID ?>&mid=<?= urlencode($MODULE_ID) ?>">
<?= bitrix_sessid_post() ?>
<?php $tabControl->Begin(); ?>
<?php $tabControl->BeginNextTab(); ?>

<tr>
    <td width="40%"><?= Loc::getMessage('BX_PRODUCTS_GIFT_OPTIONS_GIFT_PRODUCT_ID') ?></td>
    <td>
        <input type="text" name="gift_product_id" value="<?= htmlspecialchars($giftProductId) ?>" size="10" />
    </td>
</tr>
<tr>
    <td width="40%"><?= Loc::getMessage('BX_PRODUCTS_GIFT_OPTIONS_GIFT_QUANTITY') ?></td>
    <td>
        <input type="text" name="gift_quantity" value="<?= htmlspecialchars($giftQuantity) ?>" size="5" />
    </td>
</tr>

<?php $tabControl->Buttons(); ?>
<input type="submit" name="save" value="<?= Loc::getMessage('BX_PRODUCTS_GIFT_OPTIONS_SAVE') ?>" class="adm-btn-save" />
<?php $tabControl->End(); ?>
</form>
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
