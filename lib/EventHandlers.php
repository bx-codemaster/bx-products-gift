<?php

namespace Bx\Products\Gift;

use Bitrix\Main\Config\Option;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;
use Bitrix\Main\Context;

class EventHandlers
{
    private const MODULE_ID = 'bx.products.gift';

    /**
     * Handler for OnSaleBasketItemAdd event.
     * Adds the configured gift product to the basket when any product is added.
     *
     * @param BasketItem $basketItem
     */
    public static function onBasketItemAdd(BasketItem $basketItem): void
    {
        $giftProductId = (int)Option::get(self::MODULE_ID, 'gift_product_id', 0);
        $giftQuantity  = (int)Option::get(self::MODULE_ID, 'gift_quantity', 1);

        if ($giftProductId <= 0 || $giftQuantity <= 0) {
            return;
        }

        // Do not add the gift again if the item being added IS the gift product.
        if ((int)$basketItem->getField('PRODUCT_ID') === $giftProductId) {
            return;
        }

        $basket = $basketItem->getCollection();
        if (!$basket) {
            return;
        }

        // Check if the gift is already in the basket.
        foreach ($basket as $item) {
            if ((int)$item->getField('PRODUCT_ID') === $giftProductId) {
                return;
            }
        }

        // Add the gift product to the basket.
        $giftItem = $basket->createItem('catalog', $giftProductId);
        if (!($giftItem instanceof BasketItem)) {
            return;
        }

        $result = $giftItem->setFields([
            'QUANTITY'   => $giftQuantity,
            'CURRENCY'   => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
            'LID'        => Context::getCurrent()->getSite(),
            'PRODUCT_ID' => $giftProductId,
            'NAME'       => self::getProductName($giftProductId),
            'PRICE'      => 0,
            'BASE_PRICE' => 0,
            'CAN_BUY'    => 'Y',
        ]);

        if ($result instanceof \Bitrix\Main\Result && !$result->isSuccess()) {
            $basket->deleteItem($giftItem);
        }
    }

    private static function getProductName(int $productId): string
    {
        $res = \CIBlockElement::GetList(
            [],
            ['ID' => $productId, 'ACTIVE' => 'Y'],
            false,
            ['nTopCount' => 1],
            ['NAME']
        );
        $element = $res->Fetch();
        return $element ? (string)$element['NAME'] : '';
    }
}
