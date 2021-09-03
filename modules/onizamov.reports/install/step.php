<?php

if (!check_bitrix_sessid()) {
    return;
} ?>

<?php
global $APPLICATION;
if ($ex = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage(
        [
            'TYPE'    => 'ERROR',
            'MESSAGE' => 'Ошибка при установке модуля',
            'DETAILS' => $ex->GetString(),
            'HTML'    => true,
        ]
    );
} else {
    CAdminMessage::ShowNote("Модуль успешно установлен");
}
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="Назад">
</form>
