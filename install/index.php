<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

class bx_products_gift extends CModule
{
    public $MODULE_ID = 'bx.products.gift';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME         = Loc::getMessage('BX_PRODUCTS_GIFT_MODULE_NAME');
        $this->MODULE_DESCRIPTION  = Loc::getMessage('BX_PRODUCTS_GIFT_MODULE_DESCRIPTION');
        $this->PARTNER_NAME        = Loc::getMessage('BX_PRODUCTS_GIFT_PARTNER_NAME');
        $this->PARTNER_URI         = 'https://github.com/bx-codemaster';
    }

    public function DoInstall(): bool
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallEvents();
        return true;
    }

    public function DoUninstall(): bool
    {
        $this->UnInstallEvents();
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

    public function InstallEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible(
            'sale',
            'OnSaleBasketItemAdd',
            $this->MODULE_ID,
            '\Bx\Products\Gift\EventHandlers',
            'onBasketItemAdd'
        );
    }

    public function UnInstallEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleBasketItemAdd',
            $this->MODULE_ID,
            '\Bx\Products\Gift\EventHandlers',
            'onBasketItemAdd'
        );
    }
}
