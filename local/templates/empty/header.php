<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?
    $APPLICATION->ShowHead(); ?>
    <title><?
        $APPLICATION->ShowTitle(); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico"/>
</head>
<body>
<div id="panel">
    <?
    $APPLICATION->ShowPanel(); ?>
</div>
<div class="header">
    <?
    $APPLICATION->IncludeComponent(
        "bitrix:menu",
        "grey_tabs",
        [
            "COMPONENT_TEMPLATE" => "grey_tabs",
            "ROOT_MENU_TYPE" => "top",
            "MENU_CACHE_TYPE" => "N",
            "MENU_CACHE_TIME" => "3600",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "MENU_CACHE_GET_VARS" => [
            ],
            "MAX_LEVEL" => "1",
            "CHILD_MENU_TYPE" => "left",
            "USE_EXT" => "N",
            "DELAY" => "N",
            "ALLOW_MULTI_SELECT" => "N",
            "MENU_THEME" => "site",
        ],
        false
    );
    ?>
</div>
<div class="main">