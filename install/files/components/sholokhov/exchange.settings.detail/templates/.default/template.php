<?php

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die('');
}
\Bitrix\Main\Diag\Debug::dump($arResult['JS_DATA']);
CJSCore::Init(['sholokhov.exchange.detail']);

\Bitrix\Main\UI\Extension::load(['main.core.landing', 'main.core']);

/** @var CAdminTabControl $control */
$control = $arResult['CONTROL'];
$control->Begin();
?>

<div id="test"></div>

<form method="POST" id="detailSettingsForm">
    <?php
    foreach ($control->tabs as $tab) {
        $control->BeginNextTab();

        if (is_callable($tab['RENDER'])) {
            echo call_user_func($tab['RENDER'], $tab);
        }
    }

    $control->EndTab();
    ?>
    <div id="sholokhov-button-pannel"></div>
    <?php
    $control->End();
    ?>
</form>

<script>
    BX.ready(function() {
        // debugger;

        // BX.loadExt('sholokhov.exchange.detail')
        //     .then(() => {
        Sholokhov.Exchange.Detail.mounted('#test', <?= json_encode($arResult['JS_DATA']['OPTIONS']) ?>);



        //const detail = new BX.Sholokhov.Exchange.Detail.Detail(
        //    <?php //= json_encode($arResult['JS_DATA']['DATA']) ?>//,
        //    <?php //= json_encode($arResult['JS_DATA']['OPTIONS']) ?>
        //);
        //detail.view();
        // })
        // .catch(() => {
        //     alert('error')
        // })
    });
</script>