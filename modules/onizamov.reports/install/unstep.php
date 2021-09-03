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
            'MESSAGE' => 'Ошибка при отключении модуля',
            'DETAILS' => $ex->GetString(),
            'HTML'    => true,
        ]
    );
} else {
    CAdminMessage::ShowNote("Модуль успешно отключен");
}
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="Назад">
</form>
