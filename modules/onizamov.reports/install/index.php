<?

use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (class_exists("onizamov_reports")) {
    return;
}

class onizamov_reports extends CModule
{

    function __construct()
    {
        $arModuleVersion = include(dirname(__FILE__) . "/version.php");
        $this->MODULE_ID = $arModuleVersion["MODULE_ID"];
        $this->MODULE_NAME = $arModuleVersion["MODULE_NAME"];
        $this->MODULE_DESCRIPTION = $arModuleVersion["MODULE_DESCR"];
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PARTNER_NAME = $arModuleVersion["PARTNER_NAME"];
        $this->PARTNER_URI = $arModuleVersion["PARTNER_URI"];
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!CheckVersion(ModuleManager::getVersion('main'), '14.0.0')) {
            $APPLICATION->ThrowException('Ваша система не поддерживает D7');
        } else {
            ModuleManager::RegisterModule($this->MODULE_ID);
        }

        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmCompanyUpdate',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Events\\CompanyReportEvents',
            'onCrmCompanyUserFieldUpdate'
        );
        $eventManager->registerEventHandler(
            'documentgenerator',
            '\Bitrix\DocumentGenerator\Model\Document::OnAfterAdd',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Document\\DocumentTable',
            'onAfterAdd'
        );
        $eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealUpdate',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'onUpdate'
        );
        $eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealAdd',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'onAdd'
        );

        $eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealUpdate',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'OnAfterHandlerSetConnectionDealTestUpdate'
        );
        $eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealAdd',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'OnAfterHandlerSetConnectionDealTestAdd'
        );
        $eventManager->registerEventHandler(
            'crm',
            'OnBeforeCrmDealDelete',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'OnBeforeHandlerSetConnectionDealTestDelete'
        );


        $APPLICATION->IncludeAdminFile(
            "Установка модуля" . $this->MODULE_ID,
            dirname(__FILE__) . "/step.php"
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmCompanyUpdate',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Events\\CompanyReportEvents',
            'onCrmCompanyUserFieldUpdate'
        );
        $eventManager->unRegisterEventHandler(
            'documentgenerator',
            '\Bitrix\DocumentGenerator\Model\Document::OnAfterAdd',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Document\\DocumentTable',
            'onAfterAdd'
        );
        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealUpdate',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'onUpdate'
        );

        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealAdd',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'onAdd'
        );

        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealUpdate',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'OnAfterHandlerSetConnectionDealTestUpdate'
        );
        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealAdd',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'OnAfterHandlerSetConnectionDealTestAdd'
        );
        $eventManager->unRegisterEventHandler(
            'crm',
            'OnBeforeCrmDealDelete',
            'onizamov.reports',
            '\\Onizamov\\Reports\\Classes\\Crm\\Deal\\Deal',
            'OnBeforeHandlerSetConnectionDealTestDelete'
        );

        ModuleManager::UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(
            "Деинсталляция модуля " . $this->MODULE_ID,
            dirname(__FILE__) . "/unstep.php"
        );
    }
}
