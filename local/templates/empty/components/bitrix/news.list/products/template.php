<?php

use Bitrix\Main\Localization\Loc;

defined('B_PROLOG_INCLUDED') || die();

/***
 * @var array $arResult
 * @var array $arParams
 */

if (empty($arResult['ITEMS'])) {
    echo Loc::getMessage('NEWS_LIST.PRODUCTS.EMPTY_RESULT');
    return;
}

$displayFields = [
    'ID' => 'ID',
    'XML_ID' => Loc::getMessage("NEWS_LIST.PRODUCTS.XML_ID"),
    'NAME' => Loc::getMessage("NEWS_LIST.PRODUCTS.NAME"),
];

$displayProperties = [
    'VENDOR' => Loc::getMessage("NEWS_LIST.PRODUCTS.VENDOR"),
    'MATERIAL' => Loc::getMessage("NEWS_LIST.PRODUCTS.MATERIAL"),
    'QUANTITY' => Loc::getMessage("NEWS_LIST.PRODUCTS.QUANTITY"),
    'PRICE' => Loc::getMessage("NEWS_LIST.PRODUCTS.PRICE"),
];

?>
<?php
if ($arParams['DISPLAY_TOP_PAGER']): ?>
    <?= $arResult['NAV_STRING']; ?>
<?php
endif; ?>
<div class="table-container">
    <div class="table-wrapper">
        <div class="table-row table-header">
            <?php
            foreach ($displayFields as $columnName): ?>
                <div class="header-cell"><?= $columnName ?></div>
            <?php
            endforeach; ?>
            <?php
            foreach ($displayProperties as $columnName): ?>
                <div class="header-cell"><?= $columnName ?></div>
            <?php
            endforeach; ?>
        </div>
        <?php
        foreach ($arResult['ITEMS'] as $item): ?>
            <div class="table-row">
                <?php
                foreach ($displayFields as $fieldName => $columnName): ?>
                    <div class="table-cell"><?= $item[$fieldName] ?: ''; ?></div>
                <?php
                endforeach; ?>
                <?php
                foreach ($displayProperties as $propertyName => $columnName): ?>
                    <div class="table-cell"><?= $item['PROPERTIES'][$propertyName]['VALUE'] ?: ''; ?></div>
                <?php
                endforeach; ?>
            </div>
        <?php
        endforeach; ?>
    </div>
</div>
<div class="nav-container">
    <?php
    if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
        <?= $arResult['NAV_STRING']; ?>
    <?php
    endif; ?>
</div>
