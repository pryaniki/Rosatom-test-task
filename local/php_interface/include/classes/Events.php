<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;
use \Bitrix\Main\Application;

use Bitrix\Iblock\Elements\ElementMetaTagsTable;


Loc::loadMessages(__FILE__);

class Events
{
    // ex2-51
    public static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
        if ($event == 'FEEDBACK_FORM') {
            $user = CurrentUser::get();
            $userId = $user->getId();
            if (!$userId) {
                $mess = Loc::GetMessage('EX_51_NO_AUTHORIZED', ['#AUTHOR#' => $arFields['AUTHOR']]);
            } else {
                $mess = Loc::GetMessage('EX_51_AUTHORIZED', [
                    '#ID#' => $userId,
                    '#LOGIN#' => $user->getLogin(),
                    '#NAME#' => $user->getFullName(),
                    '#AUTHOR#' => $arFields['AUTHOR']
                ]);
            }

            $arFields['AUTHOR'] = $mess;

            \CEventLog::Add([
                'SEVERITY' => 'SECURITY',
                'AUDIT_TYPE_ID' => Loc::GetMessage('EX_51_REPLACE'),
                'MODULE_ID' => 'main',
                'ITEM_ID' => $event,
                'DESCRIPTION' => Loc::GetMessage('EX_51_REPLACE') . ' - '.$arFields['AUTHOR'] ,
            ]);

        }
    }

    // ex2-94
    public static function OnBeforePrologHandler()
    {

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if(!\Bitrix\Main\Loader::includeModule('iblock'))
        {
            return;
        }

        $currentPage = $request->getRequestedPageDirectory();

        $result = ElementMetaTagsTable::getList([
            'select' => ['NAME', 'TITLE', 'DESCRIPTION',],
            'filter' => ['ACTIVE' => 'Y', 'NAME' => $currentPage,]
        ]);

        if($element = $result->fetchObject())
        {
            global $APPLICATION;
            $APPLICATION->SetPageProperty('title', $element->getTitle()->getValue());
            $APPLICATION->SetPageProperty('description', $element->getDescription()->getValue());
        }
    }
    // ex2-107
    public static function clearIblockCache($arFields)
    {
        switch ($arFields['IBLOCK_ID'])
        {
            case IB_SERVISES:
                $taggedCache = Application::getInstance()->getTaggedCache();
                $taggedCache->clearByTag('iblock_id_'.$arFields['IBLOCK_ID']);
                break;
        }
    }
}