<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	'NAME' => Loc::getMessage('SIMPLECOMP_71_EXAM2_NAME'),
    'CACHE_PATH' => 'Y',
	'PATH' => array(
        'NAME' => Loc::getMessage('SIMPLECOMP_71_EXAM2_SECTION'),
		'ID' => 'ex2simple2',
	),
);
?>