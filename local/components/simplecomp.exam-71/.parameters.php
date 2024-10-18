<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = array(
	'PARAMETERS' => array(
		'PRODUCTS_IBLOCK_ID' => array(
			'NAME' => Loc::getMessage('SE2_CAT_IBLOCK_ID'),
			'TYPE' => 'STRING',
		),
        'CLASS_IBLOCK_ID' => array(
            'NAME' => Loc::getMessage('SE2_CLASS_IBLOCK_ID'),
            'TYPE' => 'STRING',
        ),
        // ex2-107
        'SERVICES_IBLOCK_ID' => array(
            'NAME' => Loc::getMessage('SE2_SERVISES_IBLOCK_ID'),
            'TYPE' => 'STRING',
        ),
		'DETAIL_PAGE_URL_TEMPLATE' => array(
			'NAME' => Loc::getMessage('SE2_DETAIL_PAGE_URL_TEMPLATE'),
			'TYPE' => 'STRING',
		),
        'PRODUCT_PROPERTY_CODE' => [
            'NAME' => Loc::getMessage('PRODUCT_PROPERTY_CODE'),
            'TYPE' => 'STRING',
        ],
        'CACHE_TIME'  =>  ['DEFAULT'=>36000000],
		'CACHE_GROUPS' => [
			'PARENT' => 'CACHE_SETTINGS',
			'NAME' => Loc::getMessage('CP_BCM_CACHE_GROUPS'),
			'TYPE' => 'CHECKBOX',
			'DEFAULT' => 'Y',
		],
	),
);