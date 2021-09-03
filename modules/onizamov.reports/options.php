<?php

use Bitrix\Main\Config\Option;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

global $APPLICATION;
$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$moduleId = 'onizamov.reports';

$optionTabs = [
    [
        "DIV"     => "edit1",
        "TAB"     => 'Настройки модуля',
        "TITLE"   => 'Настройки модуля',
        "ICON"    => "ib_settings",
        'options' => [
            'Системные настройки',
            [
                'title'        => 'Яндекс ключ для геолокации',
                'name'         => 'geocoder_api_key',
                'defaultValue' => '',
            ],
        ],
    ],
];

if ($options = $request->get("options")) {
    foreach ($options as $optionName => $optionValue) {
        Option::set($moduleId, $optionName, $optionValue);
    }
}
?>

<?php
$tabControl = new CAdminTabControl("tabControl", $optionTabs); ?>
<form method="post"
      action="<?
      echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?
      echo LANGUAGE_ID ?>">
    <?
    $tabControl->Begin(); ?>

    <?
    foreach ($optionTabs as $tab) {
        $tabControl->BeginNextTab();

        foreach ($tab['options'] as $option) {
            if (!is_array($option)) { ?>
                <tr class="heading">
                    <td colspan="2"><?= $option; ?></td>
                </tr>
                <?
            } else {
                $option['value'] = Option::get($moduleId, $option['name'], $option['defaultValue']);
                ?>
                <tr>
                    <td><?= $option['title'] ?></td>
                    <td><input name="options[<?= $option['name'] ?>]" value="<?= $option['value'] ?>" type="text"></td>
                </tr>
                <?
            }
        }
    } ?>

    <?
    $tabControl->Buttons(); ?>
    <input type="submit" name="Update" value="<?= GetMessage("MAIN_SAVE") ?>"
           title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save">
    <input type="submit" name="Apply" value="<?= GetMessage("MAIN_OPT_APPLY") ?>"
           title="<?= GetMessage("MAIN_OPT_APPLY_TITLE") ?>">
    <?
    if (strlen($request->get("back_url_settings")) > 0): ?>
        <input type="button" name="Cancel" value="<?= GetMessage("MAIN_OPT_CANCEL") ?>"
               title="<?= GetMessage("MAIN_OPT_CANCEL_TITLE") ?>"
               onclick="window.location='<?
               echo htmlspecialcharsbx(CUtil::addslashes($request->get("back_url_settings"))) ?>'">
        <input type="hidden" name="back_url_settings"
               value="<?= htmlspecialcharsbx($request->get("back_url_settings")) ?>">
    <?
    endif ?>
    <input type="submit" name="RestoreDefaults" title="<?
    echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           OnClick="return confirm('<?
           echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?
           echo GetMessage("MAIN_RESTORE_DEFAULTS") ?>">
    <?= bitrix_sessid_post(); ?>
    <?
    $tabControl->End(); ?>
</form>
