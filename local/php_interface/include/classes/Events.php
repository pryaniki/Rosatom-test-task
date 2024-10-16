<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\CurrentUser;

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
}