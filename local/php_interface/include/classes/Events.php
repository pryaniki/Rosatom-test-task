<?php
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Events
{
    // ex2-51
    public static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
        if ($event == 'FEEDBACK_FORM') {
            global $USER;
			Debug::dumpToFile($arFields, 'test');

            if (!$USER->IsAuthorized()) {
                $mess = Loc::GetMessage('EX_51_NO_AUTHORIZED', ['#AUTHOR#' => $arFields['AUTHOR']]);
            } else {
                $mess = Loc::GetMessage('EX_51_AUTHORIZED', [
                    '#ID#' => $USER->GetID(),
                    '#LOGIN#' => $USER->GetLogin(),
                    '#NAME#' => $USER->GetFullName(),
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
        Debug::dumpToFile($arFields, 'test');
    }
}